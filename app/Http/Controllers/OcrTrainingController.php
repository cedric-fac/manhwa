<?php

namespace App\Http\Controllers;

use App\Models\OcrTrainingData;
use App\Models\Reading;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OcrTrainingController extends Controller
{
    /**
     * Display the OCR training dashboard.
     */
    public function index()
    {
        return Inertia::render('Ocr/Dashboard', [
            'statistics' => OcrTrainingData::getStatistics(),
            'pending_reviews' => OcrTrainingData::with('reading.client')
                ->where(function ($query) {
                    $query->where('verified', false)
                        ->orWhere('confidence', '<', 80);
                })
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Show the OCR review interface.
     */
    public function review(OcrTrainingData $trainingData)
    {
        return Inertia::render('Ocr/Review', [
            'training_data' => $trainingData->load('reading.client'),
        ]);
    }

    /**
     * Update the OCR training data with corrections.
     */
    public function update(Request $request, OcrTrainingData $trainingData)
    {
        $validated = $request->validate([
            'corrected_text' => ['required', 'string'],
            'feedback' => ['nullable', 'string'],
        ]);

        $trainingData->addCorrection($validated['corrected_text']);

        if ($request->has('feedback')) {
            $trainingData->update([
                'metadata' => array_merge($trainingData->metadata ?? [], [
                    'feedback' => $validated['feedback'],
                    'corrected_at' => now()->toIso8601String(),
                ])
            ]);
        }

        return redirect()
            ->route('ocr.dashboard')
            ->with('success', 'Correction enregistrée avec succès.');
    }

    /**
     * Store a new OCR training sample.
     */
    public function store(Reading $reading, Request $request)
    {
        $validated = $request->validate([
            'original_text' => ['required', 'string'],
            'confidence' => ['required', 'numeric', 'between:0,100'],
            'metadata' => ['nullable', 'array'],
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => $validated['original_text'],
            'corrected_text' => (string) $reading->value,
            'confidence' => $validated['confidence'],
            'metadata' => $validated['metadata'],
            'image_url' => $reading->photo_url,
            'verified' => false,
        ]);

        return response()->json([
            'message' => 'Training data stored successfully',
            'training_data_id' => $trainingData->id
        ]);
    }

    /**
     * Get OCR training statistics.
     */
    public function statistics()
    {
        return response()->json([
            'statistics' => OcrTrainingData::getStatistics()
        ]);
    }
}