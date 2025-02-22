<?php

namespace App\Http\Controllers;

use App\Models\Reading;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ReadingController extends Controller
{
    /**
     * Display a listing of the readings for a client.
     */
    public function index(Client $client)
    {
        return Inertia::render('Readings/Index', [
            'client' => $client->load(['readings' => function($query) {
                $query->latest('read_at')->paginate(10);
            }])
        ]);
    }

    /**
     * Show the form for creating a new reading.
     */
    public function create(Client $client)
    {
        return Inertia::render('Readings/Create', [
            'client' => $client
        ]);
    }

    /**
     * Store a newly created reading.
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0'],
            'read_at' => ['required', 'date'],
            'photo' => ['required', 'image', 'max:5120'], // Max 5MB
            'ocr_data' => ['nullable', 'array'],
            'ocr_data.confidence' => ['required_with:ocr_data', 'numeric', 'between:0,100'],
            'ocr_data.suggestions' => ['required_with:ocr_data', 'array'],
            'ocr_data.text' => ['required_with:ocr_data', 'string'],
        ]);

        // Upload photo to Cloudinary
        $photo_url = null;
        if ($request->hasFile('photo')) {
            $path = Storage::disk('cloudinary')->put(
                'meter-readings/' . $client->id,
                $request->file('photo')
            );
            $photo_url = Storage::disk('cloudinary')->url($path);
        }

        // Prepare OCR metadata if provided
        $metadata = null;
        if ($request->has('ocr_data')) {
            $metadata = [
                'ocr' => array_merge($request->input('ocr_data'), [
                    'processed_at' => now()->toIso8601String(),
                    'selected_value' => $validated['value']
                ])
            ];
        }

        $reading = $client->readings()->create([
            'value' => $validated['value'],
            'read_at' => $validated['read_at'],
            'photo_url' => $photo_url,
            'metadata' => $metadata,
            'synced' => true
        ]);

        return redirect()->route('readings.show', [$client->id, $reading->id])
            ->with('success', 'Relevé créé avec succès.');
    }

    /**
     * Display the specified reading.
     */
    public function show(Client $client, Reading $reading)
    {
        return Inertia::render('Readings/Show', [
            'client' => $client,
            'reading' => $reading->load('invoice')
        ]);
    }

    /**
     * Sync offline readings.
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'readings' => ['required', 'array'],
            'readings.*.client_id' => ['required', 'exists:clients,id'],
            'readings.*.value' => ['required', 'numeric', 'min:0'],
            'readings.*.read_at' => ['required', 'date'],
            'readings.*.photo' => ['sometimes', 'string'], // Base64 encoded image
            'readings.*.ocr_data' => ['nullable', 'array'],
            'readings.*.ocr_data.confidence' => ['required_with:readings.*.ocr_data', 'numeric', 'between:0,100'],
            'readings.*.ocr_data.suggestions' => ['required_with:readings.*.ocr_data', 'array'],
            'readings.*.ocr_data.text' => ['required_with:readings.*.ocr_data', 'string'],
        ]);

        $results = [];

        foreach ($validated['readings'] as $readingData) {
            try {
                // Handle base64 image if present
                $photo_url = null;
                if (!empty($readingData['photo'])) {
                    $imageData = base64_decode($readingData['photo']);
                    $path = Storage::disk('cloudinary')->put(
                        'meter-readings/' . $readingData['client_id'],
                        $imageData
                    );
                    $photo_url = Storage::disk('cloudinary')->url($path);
                }

                // Prepare OCR metadata if provided
                $metadata = null;
                if (!empty($readingData['ocr_data'])) {
                    $metadata = [
                        'ocr' => array_merge($readingData['ocr_data'], [
                            'processed_at' => now()->toIso8601String(),
                            'selected_value' => $readingData['value']
                        ])
                    ];
                }

                $reading = Reading::create([
                    'client_id' => $readingData['client_id'],
                    'value' => $readingData['value'],
                    'read_at' => $readingData['read_at'],
                    'photo_url' => $photo_url,
                    'metadata' => $metadata,
                    'synced' => true
                ]);

                $results[] = [
                    'success' => true,
                    'reading_id' => $reading->id,
                    'client_id' => $readingData['client_id']
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'client_id' => $readingData['client_id']
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
