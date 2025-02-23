<?php

namespace App\Http\Controllers;

use App\Models\OcrTrainingData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OcrTrainingController extends Controller
{
    /**
     * Constructor to require authentication
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

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
                'reviewed_at' => now()->toIso8601String(),
                'reviewer_id' => $request->user()->id
            ])
        ]);

        event('ocr.training.reviewed', $trainingData);

        return redirect()
            ->route('ocr.dashboard')
            ->with('success', 'Correction enregistrée avec succès');
    }

    /**
     * Display OCR performance statistics.
     */
    public function statistics()
    {
        Gate::authorize('view-ocr-statistics');

        $stats = OcrTrainingData::getStatistics();
        
        return Inertia::render('Ocr/Statistics', [
            'stats' => $stats
        ]);
    }
}