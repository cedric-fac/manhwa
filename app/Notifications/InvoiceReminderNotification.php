<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    use Queueable;

    protected $invoice;
    protected $pdf;
    protected $daysUntilDue;

    /**
     * Create a new notification instance.
     */
    public function __construct($invoice, $pdf = null)
    {
        $this->invoice = $invoice;
        $this->pdf = $pdf;
        $this->daysUntilDue = now()->startOfDay()->diffInDays($invoice->due_date, false);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Rappel - Facture {$this->invoice->number}")
            ->view('emails.invoice-reminder', [
                'invoice' => $this->invoice,
                'daysUntilDue' => $this->daysUntilDue,
                'url' => route('invoices.show', $this->invoice->id)
            ]);

        if ($this->pdf) {
            $message->attachData(
                $this->pdf,
                "facture_{$this->invoice->number}.pdf",
                ['mime' => 'application/pdf']
            );
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
