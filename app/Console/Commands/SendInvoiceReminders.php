<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-invoice-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for upcoming and overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending invoice reminders...');

        // Get configuration
        $reminders = config('billing.reminders');
        
        // Get invoices that need reminders
        $invoices = \App\Models\Invoice::query()
            ->where('status', '!=', \App\Enums\InvoiceStatus::PAID)
            ->where(function ($query) use ($reminders) {
                // Upcoming due dates
                $query->where(function ($q) use ($reminders) {
                    $q->whereDate('due_date', '=', now()->addDays($reminders['first']))
                      ->orWhereDate('due_date', '=', now()->addDays($reminders['second']));
                })
                // Overdue invoices
                ->orWhere(function ($q) use ($reminders) {
                    $q->whereDate('due_date', '<=', now()->subDays($reminders['overdue']))
                      ->where('status', '!=', \App\Enums\InvoiceStatus::OVERDUE);
                });
            })
            ->with(['client'])
            ->get();

        $this->info("Found {$invoices->count()} invoices to process.");

        foreach ($invoices as $invoice) {
            try {
                // Generate PDF
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
                    'invoice' => $invoice
                ]);

                // Determine reminder type
                $reminderType = match (true) {
                    $invoice->due_date->isSameDay(now()->addDays($reminders['first'])) => 'first',
                    $invoice->due_date->isSameDay(now()->addDays($reminders['second'])) => 'second',
                    $invoice->due_date <= now()->subDays($reminders['overdue']) => 'overdue',
                    default => null
                };

                if (!$reminderType) {
                    continue;
                }

                // Check if reminder has already been sent
                if (\App\Models\InvoiceReminder::hasBeenSent($invoice->id, $reminderType)) {
                    $this->line("Reminder '{$reminderType}' already sent for invoice {$invoice->number}");
                    continue;
                }

                // Create reminder record
                $reminder = \App\Models\InvoiceReminder::create([
                    'invoice_id' => $invoice->id,
                    'type' => $reminderType,
                ]);

                // Send reminder with PDF
                $invoice->client->notify(new \App\Notifications\InvoiceReminderNotification(
                    $invoice,
                    $pdf->output()
                ));

                // Mark reminder as sent
                $reminder->markAsSent();

                // Update status for overdue invoices
                if ($reminderType === 'overdue') {
                    $invoice->update(['status' => \App\Enums\InvoiceStatus::OVERDUE]);
                }

                $this->info("Sent {$reminderType} reminder for invoice {$invoice->number}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for invoice {$invoice->number}: {$e->getMessage()}");
            }
        }

        $this->info('Invoice reminders sent successfully.');
    }
}
