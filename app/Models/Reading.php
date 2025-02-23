<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Reading extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'value',
        'photo_url',
        'read_at',
        'synced',
        'metadata'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'read_at' => 'datetime',
        'synced' => 'boolean',
        'metadata' => 'array'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function trainingData(): HasOne
    {
        return $this->hasOne(OcrTrainingData::class);
    }

    public function isInvoiced(): bool
    {
        return $this->invoice()->exists();
    }

    public function addPhotoFromFile(UploadedFile $file): void
    {
        $path = Storage::disk('cloudinary')->put(
            'meter-readings/' . $this->client_id,
            $file
        );

        $photoUrl = Storage::disk('cloudinary')->url($path);

        $this->update([
            'photo_url' => $photoUrl
        ]);
    }

    public function hasOcrData(): bool
    {
        return isset($this->metadata['ocr']);
    }

    public function getOcrConfidence(): ?float
    {
        return $this->metadata['ocr']['confidence'] ?? null;
    }

    public function addOcrMetadata(array $ocrData): void
    {
        $this->metadata = array_merge($this->metadata ?? [], [
            'ocr' => array_merge([
                'processed_at' => now()->toIso8601String()
            ], $ocrData)
        ]);
        $this->save();
    }

    protected function formatValue($value)
    {
        return is_numeric($value) ? number_format($value, 2, '.', '') : $value;
    }
}
