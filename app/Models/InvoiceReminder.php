<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'invoice_id',
        'type',
        'sent',
        'sent_at',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the invoice that owns the reminder.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Mark reminder as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'sent' => true,
            'sent_at' => now(),
            'error' => null,
        ]);
    }

    /**
     * Record sending error.
     */
    public function recordError(string $error): void
    {
        $this->update([
            'sent' => false,
            'error' => $error,
        ]);
    }

    /**
     * Check if a reminder type has been sent for an invoice.
     */
    public static function hasBeenSent(int $invoiceId, string $type): bool
    {
        return static::where('invoice_id', $invoiceId)
            ->where('type', $type)
            ->where('sent', true)
            ->exists();
    }
}
