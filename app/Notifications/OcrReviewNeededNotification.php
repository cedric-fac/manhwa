<?php

namespace App\Notifications;

use App\Models\OcrTrainingData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OcrReviewNeededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected OcrTrainingData $trainingData
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Révision OCR nécessaire')
            ->line('Un relevé nécessite une révision OCR.')
            ->line("Client: {$this->trainingData->reading->client->name}")
            ->line("Confiance OCR: {$this->trainingData->confidence}%")
            ->line("Texte détecté: {$this->trainingData->original_text}")
            ->action('Réviser maintenant', route('ocr.review', $this->trainingData->id))
            ->line('La révision des résultats aide à améliorer la précision de l\'OCR.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'training_data_id' => $this->trainingData->id,
            'client_id' => $this->trainingData->reading->client_id,
            'confidence' => $this->trainingData->confidence,
            'original_text' => $this->trainingData->original_text,
        ];
    }
}