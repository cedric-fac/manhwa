<?php

namespace App\Support;

use App\Models\OcrTrainingData;
use App\Models\Reading;
use App\Notifications\OcrReviewNeededNotification;
use Illuminate\Support\Facades\Log;

class OcrManager
{
    /**
     * The confidence threshold below which OCR results need review.
     */
    public const CONFIDENCE_THRESHOLD = 80;

    /**
     * Store OCR results and create training data.
     */
    public static function storeResults(Reading $reading, array $ocrData): OcrTrainingData
    {
        // Create training data record
        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => $ocrData['text'],
            'confidence' => $ocrData['confidence'],
            'metadata' => [
                'suggestions' => $ocrData['suggestions'] ?? [],
                'processing_time' => $ocrData['processing_time'] ?? null,
                'processed_at' => now()->toIso8601String(),
            ],
            'image_url' => $reading->photo_url,
            'verified' => false
        ]);

        // Log OCR results
        Log::info('OCR results stored', [
            'reading_id' => $reading->id,
            'confidence' => $ocrData['confidence'],
            'training_data_id' => $trainingData->id
        ]);

        // Send notifications if confidence is low
        if (self::needsReview($trainingData)) {
            self::notifyReviewers($trainingData);
        }

        return $trainingData;
    }

    /**
     * Check if OCR results need review.
     */
    public static function needsReview(OcrTrainingData $trainingData): bool
    {
        return $trainingData->confidence < self::CONFIDENCE_THRESHOLD || !$trainingData->verified;
    }

    /**
     * Notify OCR reviewers about results that need review.
     */
    protected static function notifyReviewers(OcrTrainingData $trainingData): void
    {
        // Get all admin users
        $admins = \App\Models\User::where('is_admin', true)->get();
        
        // Send notification to each admin
        foreach ($admins as $admin) {
            $admin->notify(new OcrReviewNeededNotification($trainingData));
        }

        Log::info('OCR review notifications sent', [
            'training_data_id' => $trainingData->id,
            'admin_count' => $admins->count()
        ]);
    }

    /**
     * Calculate OCR accuracy improvement.
     */
    public static function calculateAccuracyImprovement(string $original, string $corrected): float
    {
        if (empty($original) || empty($corrected)) {
            return 0.0;
        }

        similar_text($original, $corrected, $percentage);
        return $percentage;
    }

    /**
     * Format OCR results for display.
     */
    public static function formatResults(OcrTrainingData $trainingData): array
    {
        return [
            'id' => $trainingData->id,
            'original_text' => $trainingData->original_text,
            'corrected_text' => $trainingData->corrected_text,
            'confidence' => round($trainingData->confidence, 2),
            'verified' => $trainingData->verified,
            'suggestions' => $trainingData->metadata['suggestions'] ?? [],
            'processed_at' => $trainingData->metadata['processed_at'],
            'needs_review' => self::needsReview($trainingData),
        ];
    }
}