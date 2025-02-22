<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = [
        'client_id',
        'value',
        'photo_url',
        'read_at',
        'synced',
        'metadata'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'synced' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Check if the reading was processed by OCR.
     */
    public function isOcrProcessed(): bool
    {
        return isset($this->metadata['ocr']);
    }

    /**
     * Get the OCR confidence score.
     */
    public function getOcrConfidence(): ?float
    {
        return $this->metadata['ocr']['confidence'] ?? null;
    }

    /**
     * Get all OCR suggestions.
     */
    public function getOcrSuggestions(): array
    {
        return $this->metadata['ocr']['suggestions'] ?? [];
    }

    /**
     * Add OCR metadata to the reading.
     */
    public function addOcrMetadata(array $ocrData): void
    {
        $this->metadata = array_merge($this->metadata ?? [], [
            'ocr' => array_merge([
                'processed_at' => now()->toIso8601String()
            ], $ocrData)
        ]);
        $this->save();
    }

    protected $casts = [
        'value' => 'decimal:2',
        'read_at' => 'datetime',
        'synced' => 'boolean'
    ];

    /**
     * Get the client that owns the reading.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the associated invoice if exists.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
