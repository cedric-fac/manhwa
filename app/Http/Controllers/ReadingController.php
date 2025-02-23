<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Reading;
use App\Models\OcrTrainingData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ReadingController extends Controller
{
    public function index(Request $request, Client $client)
    {
        $readings = $client->readings()
            ->with(['trainingData'])
            ->latest('read_at')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'client' => $client,
                'readings' => $readings
            ]);
        }

        return Inertia::render('Readings/Index', [
            'client' => $client,
            'readings' => $readings
        ]);
    }

    public function create(Request $request, Client $client)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'client' => $client
            ]);
        }

        return Inertia::render('Readings/Create', [
            'client' => $client
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0'],
            'read_at' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'ocr_data' => ['nullable', 'array'],
            'ocr_data.confidence' => ['nullable', 'numeric'],
            'ocr_data.text' => ['nullable', 'string'],
            'ocr_data.suggestions' => ['nullable', 'array'],
        ]);

        $reading = new Reading([
            'value' => $validated['value'],
            'read_at' => $validated['read_at'],
            'synced' => true
        ]);

        $reading->client()->associate($client);
        $reading->save();

        if ($request->hasFile('photo')) {
            // Store file in the appropriate disk
            $disk = config('filesystems.default');
            $path = Storage::disk($disk)->putFile(
                "meter-readings/{$client->id}",
                $request->file('photo')
            );

            // Get the URL for the stored file
            $url = Storage::disk($disk)->url($path);

            // Update reading with photo URL
            $reading->update([
                'photo_url' => $url
            ]);
        }

        // Store OCR training data if available
        if (isset($validated['ocr_data'])) {
            OcrTrainingData::create([
                'reading_id' => $reading->id,
                'original_text' => $validated['ocr_data']['text'],
                'corrected_text' => null,
                'confidence' => $validated['ocr_data']['confidence'],
                'metadata' => [
                    'suggestions' => $validated['ocr_data']['suggestions'] ?? [],
                    'processed_at' => now()->toIso8601String()
                ],
                'image_url' => $reading->photo_url,
                'verified' => false
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Relevé enregistré avec succès',
                'reading' => $reading->load('trainingData')
            ]);
        }

        return redirect()
            ->route('readings.index', $client)
            ->with('success', 'Relevé enregistré avec succès.');
    }

    public function show(Request $request, Client $client, Reading $reading)
    {
        $reading->load(['trainingData']);

        if ($request->wantsJson()) {
            return response()->json([
                'client' => $client,
                'reading' => $reading
            ]);
        }

        return Inertia::render('Readings/Show', [
            'client' => $client,
            'reading' => $reading
        ]);
    }
}
