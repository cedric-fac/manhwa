<?php

namespace App\Models;

use App\Notifications\OcrReviewNeededNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;

class OcrTrainingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading_id',
        'original_text',
        'corrected_text',
        'confidence',
        'metadata',
        'image_url',
        'verified'
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'metadata' => 'array',
        'verified' => 'boolean'
    ];

    /**
     * Get the reading that owns the training data.
     */
    public function reading(): BelongsTo
    {
        return $this->belongsTo(Reading::class);
    }

    /**
     * Check if this training data needs review.
     */
    public function needsReview(): bool
    {
        return !$this->verified || $this->confidence < 80;
    }

    /**
     * Mark this training data as verified.
     */
    public function markAsVerified(): void
    {
        $this->update(['verified' => true]);
    }

    /**
     * Add correction to the training data.
     */
    public function addCorrection(string $correctedText): void
    {
        $this->update([
            'corrected_text' => $correctedText,
            'verified' => true
        ]);
    }

    /**
     * Get the improvement ratio between original and corrected text.
     */
    public function getImprovementRatio(): float
    {
        if (!$this->verified || !$this->corrected_text) {
            return 0;
        }

        return similar_text($this->original_text, $this->corrected_text) / 100;
    }

    /**
     * Scope query to unverified entries.
     */
    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }

    /**
     * Scope query to low confidence entries.
     */
    public function scopeLowConfidence($query, float $threshold = 80)
    {
        return $query->where('confidence', '<', $threshold);
    }

    /**
     * Get the training data statistics.
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'verified' => self::where('verified', true)->count(),
            'low_confidence' => self::where('confidence', '<', 80)->count(),
            'avg_confidence' => self::avg('confidence'),
            'needs_review' => self::where('verified', false)
                ->orWhere('confidence', '<', 80)
                ->count()
        ];
    }

    /**
     * Send notifications for review if needed.
     */
    public function sendReviewNotificationIfNeeded(): void
    {
        if ($this->needsReview()) {
            // Get all admin users to notify
            $admins = User::where('is_admin', true)->get();
            
            // Send notification to all admins
            Notification::send($admins, new OcrReviewNeededNotification($this));
        }
    }

    /**
     * The "created" event handler.
     */
    protected static function booted()
    {
        static::created(function ($trainingData) {
            $trainingData->sendReviewNotificationIfNeeded();
        });
    }
}
