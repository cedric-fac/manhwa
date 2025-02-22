<?php

namespace App\Http\Controllers;

use App\Models\OcrTrainingData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OcrTrainingController extends Controller
{
    /**
     * Display the OCR training dashboard.
     */
    public function index()
    {
        // Check if user can review OCR
        Gate::authorize('review-ocr');

        $stats = OcrTrainingData::getStatistics();
        $pendingReviews = OcrTrainingData::with('reading.client')
            ->where('verified', false)
            ->orWhere('confidence', '<', 80)
            ->latest()
            ->paginate(10);

        return Inertia::render('Ocr/Dashboard', [
            'stats' => $stats,
            'pendingReviews' => $pendingReviews
        ]);
    }

    /**
     * Display the OCR review interface.
     */
    public function review(OcrTrainingData $trainingData)
    {
        Gate::authorize('review-ocr');

        $trainingData->load('reading.client');

        return Inertia::render('Ocr/Review', [
            'trainingData' => $trainingData
        ]);
    }

    /**
     * Update OCR training data with corrections.
     */
    public function update(Request $request, OcrTrainingData $trainingData)
    {
        Gate::authorize('review-ocr');

        $validated = $request->validate([
            'corrected_text' => 'required|string',
            'feedback' => 'nullable|string'
        ]);

        $trainingData->update([
            'corrected_text' => $validated['corrected_text'],
            'verified' => true,
            'metadata' => array_merge($trainingData->metadata ?? [], [
                'feedback' => $validated['feedback'],
                'reviewed_at' => now()->toIso8601String()
            ])
        ]);

        event('ocr.training.reviewed', $trainingData);

        return redirect()
            ->route('ocr.dashboard')
            ->with('success', 'Correction enregistrée avec succès');
    }

    /**
     * Store new OCR training data.
     */
    public function store(Request $request, $readingId)
    {
        $validated = $request->validate([
            'original_text' => 'required|string',
            'confidence' => 'required|numeric',
            'metadata' => 'required|array'
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $readingId,
            'original_text' => $validated['original_text'],
            'confidence' => $validated['confidence'],
            'metadata' => $validated['metadata'],
            'verified' => false
        ]);

        event('ocr.training.created', $trainingData);

        return response()->json([
            'message' => 'Données d\'entraînement OCR enregistrées',
            'training_data' => $trainingData
        ]);
    }

    /**
     * Display OCR performance statistics.
     */
    public function statistics()
    {
        Gate::authorize('review-ocr');

        $stats = OcrTrainingData::getStatistics();
        
        return Inertia::render('Ocr/Statistics', [
            'stats' => $stats
        ]);
    }
}