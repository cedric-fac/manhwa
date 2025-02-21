<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification
{
    use Queueable;

    protected $invoice;
    protected $pdfContent;

    /**
     * Create a new notification instance.
     */
    public function __construct($invoice, $pdfContent)
    {
        $this->invoice = $invoice;
        $this->pdfContent = $pdfContent;
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
        return (new MailMessage)
            ->subject("Facture {$this->invoice->number}")
            ->view('emails.invoice', [
                'invoice' => $this->invoice,
                'url' => route('invoices.show', $this->invoice->id)
            ])
            ->attachData(
                $this->pdfContent,
                "facture_{$this->invoice->number}.pdf",
                ['mime' => 'application/pdf']
            );
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
