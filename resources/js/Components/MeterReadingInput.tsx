import React, { useCallback, useState } from 'react';
import { useOCR, type OCRResult } from '@/lib/ocr';
import route from 'ziggy-js';

interface Props {
    onReadingCapture: (reading: number, imageFile: File) => void;
    isUploading?: boolean;
    error?: string;
    readingId?: number;
}

export function MeterReadingInput({ onReadingCapture, isUploading, error, readingId }: Props) {
    const [preview, setPreview] = useState<string | null>(null);
    const [suggestions, setSuggestions] = useState<number[]>([]);
    const [selectedReading, setSelectedReading] = useState<number | null>(null);
    const { processImage, isProcessing, error: ocrError } = useOCR();

    const storeOcrTraining = async (imageFile: File, result: OCRResult) => {
        if (!readingId) return;

        try {
            await fetch(route('ocr.store', readingId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    original_text: result.text,
                    confidence: result.confidence,
                    metadata: {
                        numbers: result.numbers,
                        processed_at: new Date().toISOString()
                    }
                })
            });
        } catch (error) {
            console.error('Failed to store OCR training data:', error);
        }
    };

    const handleImageCapture = useCallback(async (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file) return;

        // Create preview
        const imageUrl = URL.createObjectURL(file);
        setPreview(imageUrl);

        try {
            // Process image with OCR
            const result = await processImage(file);
            if (result?.numbers.length) {
                setSuggestions(result.numbers);
                setSelectedReading(result.numbers[0]); // Select the first (most likely) reading
                await storeOcrTraining(file, result);
            }
        } catch (err) {
            console.error('Failed to process image:', err);
        }
    }, [processImage]);

    const handleSubmit = useCallback(() => {
        const file = document.querySelector<HTMLInputElement>('input[type="file"]')?.files?.[0];
        if (file && selectedReading !== null) {
            onReadingCapture(selectedReading, file);
        }
    }, [selectedReading, onReadingCapture]);

    return (
        <div className="space-y-4">
            <div className="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                <input
                    type="file"
                    accept="image/*"
                    capture="environment"
                    onChange={handleImageCapture}
                    className="hidden"
                    id="meter-photo"
                    disabled={isProcessing || isUploading}
                />
                
                {preview ? (
                    <div className="relative w-full max-w-md">
                        <img 
                            src={preview} 
                            alt="Meter Reading" 
                            className="w-full rounded-lg shadow-lg"
                        />
                        <button
                            type="button"
                            onClick={() => {
                                setPreview(null);
                                setSuggestions([]);
                                setSelectedReading(null);
                            }}
                            className="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                ) : (
                    <label
                        htmlFor="meter-photo"
                        className="flex flex-col items-center justify-center w-full h-48 cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors"
                    >
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span className="mt-2 text-sm text-gray-500">
                            {isProcessing ? 'Analyse en cours...' : 'Prendre une photo du compteur'}
                        </span>
                    </label>
                )}
            </div>

            {suggestions.length > 0 && (
                <div className="space-y-2">
                    <label className="block text-sm font-medium text-gray-700">
                        Valeur du compteur
                    </label>
                    <div className="flex gap-2 flex-wrap">
                        {suggestions.map((reading, index) => (
                            <button
                                key={`${reading}-${index}`}
                                type="button"
                                onClick={() => setSelectedReading(reading)}
                                className={`px-3 py-1 rounded-full text-sm ${
                                    selectedReading === reading
                                        ? 'bg-blue-500 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                {reading}
                            </button>
                        ))}
                    </div>
                    <div className="flex items-center gap-2 mt-4">
                        <input
                            type="number"
                            value={selectedReading ?? ''}
                            onChange={(e) => setSelectedReading(Number(e.target.value))}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Valeur du compteur"
                        />
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={selectedReading === null || isUploading}
                            className="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
                        >
                            Valider
                        </button>
                    </div>
                </div>
            )}

            {(error || ocrError) && (
                <p className="text-red-500 text-sm mt-2">
                    {error || ocrError}
                </p>
            )}
        </div>
    );
}