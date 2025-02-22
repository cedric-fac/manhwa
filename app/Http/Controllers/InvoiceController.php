<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Reading;
use App\Models\Client;
use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with(['client', 'reading'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->client_id, function($query, $clientId) {
                return $query->where('client_id', $clientId);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'client_id'])
        ]);
    }

    /**
     * Generate a new invoice from a reading.
     */
    public function generate(Reading $reading)
    {
        // Check if invoice already exists for this reading
        if ($reading->invoice()->exists()) {
            return back()->with('error', 'Une facture existe déjà pour ce relevé.');
        }

        try {
            DB::beginTransaction();

            // Calculate amounts
            $amounts = Invoice::calculateAmounts($reading);

            // Generate invoice number
            $sequence = DB::table('invoices')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;

            $invoiceNumber = str_replace(
                ['{Y}', '{m}', '{sequence}'],
                [
                    date('Y'),
                    date('m'),
                    str_pad($sequence, config('billing.sequence_digits', 4), '0', STR_PAD_LEFT)
                ],
                config('billing.invoice_number_format')
            );

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $reading->client_id,
                'reading_id' => $reading->id,
                'number' => $invoiceNumber,
                'amount_ht' => $amounts['amount_ht'],
                'tva' => $amounts['tva'],
                'amount_ttc' => $amounts['amount_ttc'],
                'status' => InvoiceStatus::DRAFT,
                'due_date' => now()->addDays(config('billing.payment_term_days', 30))
            ]);

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture générée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la génération de la facture.');
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice->load(['client', 'reading'])
        ]);
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_column(InvoiceStatus::cases(), 'value'))]
        ]);

        try {
            DB::beginTransaction();

            $invoice->update([
                'status' => $validated['status'],
                'paid_at' => $validated['status'] === InvoiceStatus::PAID->value ? now() : null
            ]);

            // Create a "paid" reminder record if status is PAID
            if ($validated['status'] === InvoiceStatus::PAID->value) {
                $invoice->reminders()->create([
                    'type' => 'payment',
                    'sent' => true,
                    'sent_at' => now()
                ]);
            }

            DB::commit();
            return back()->with('success', 'Statut de la facture mis à jour.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour du statut.');
        }
    }

    /**
     * Download invoice PDF.
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['client', 'reading']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice
        ]);

        return $pdf->download("facture_{$invoice->number}.pdf");
    }

    /**
     * Send invoice by email.
     */
    public function send(Invoice $invoice)
    {
        $invoice->load(['client', 'reading']);

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice
        ]);
        
        // Send notification with PDF attachment
        $invoice->client->notify(new \App\Notifications\InvoiceNotification(
            $invoice,
            $pdf->output()
        ));

        // Update invoice status
        $invoice->update(['status' => InvoiceStatus::SENT]);

        return back()->with('success', 'Facture envoyée avec succès.');
    }
}
