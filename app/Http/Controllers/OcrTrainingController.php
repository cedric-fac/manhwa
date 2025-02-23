<?php

namespace App\Http\Controllers;

use App\Models\OcrTrainingData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class OcrTrainingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        Gate::authorize('review-ocr');

        $stats = OcrTrainingData::getStatistics();
        $pendingReviews = OcrTrainingData::with('reading.client')
            ->where('verified', false)
            ->orWhere('confidence', '<', 80)
            ->latest()
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'stats' => $stats,
                'pendingReviews' => $pendingReviews
            ]);
        }

        return Inertia::render('Ocr/Dashboard', [
            'stats' => $stats,
            'pendingReviews' => $pendingReviews
        ]);
    }

    public function review(Request $request, OcrTrainingData $trainingData)
    {
        Gate::authorize('review-ocr');

        $trainingData->load('reading.client');

        if ($request->wantsJson()) {
            return response()->json([
                'trainingData' => $trainingData
            ]);
        }

        return Inertia::render('Ocr/Review', [
            'trainingData' => $trainingData
        ]);
    }

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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Correction enregistrée avec succès',
                'trainingData' => $trainingData
            ]);
        }

        return redirect()
            ->route('ocr.dashboard')
            ->with('success', 'Correction enregistrée avec succès');
    }

    public function statistics(Request $request)
    {
        Gate::authorize('view-ocr-statistics');

        $stats = OcrTrainingData::getStatistics();
        
        if ($request->wantsJson()) {
            return response()->json([
                'stats' => $stats
            ]);
        }

        return Inertia::render('Ocr/Statistics', [
            'stats' => $stats
        ]);
    }
}