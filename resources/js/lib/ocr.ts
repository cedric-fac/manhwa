import { createWorker } from 'tesseract.js';
import { useState, useEffect } from 'react';

export interface OCRResult {
    text: string;
    confidence: number;
    numbers: number[];
}

type OCRWorker = Awaited<ReturnType<typeof createWorker>>;

export class OCRService {
    private static worker: OCRWorker | null = null;
    private static isInitializing = false;
    private static initPromise: Promise<void> | null = null;

    private static async getWorker(): Promise<OCRWorker> {
        if (this.worker) return this.worker;

        if (this.initPromise) {
            await this.initPromise;
            return this.worker!;
        }

        this.isInitializing = true;
        this.initPromise = this.initializeWorker();
        await this.initPromise;
        this.isInitializing = false;
        return this.worker!;
    }

    private static async initializeWorker(): Promise<void> {
        const worker = await createWorker();

        if (import.meta.env.DEV) {
            console.log('OCR: Initializing worker');
        }
        
        // Initialize with English language
        await worker.reinitialize('eng+osd');
        this.worker = worker;

        if (import.meta.env.DEV) {
            console.log('OCR: Worker initialized');
        }
    }

    public static async processImage(imageSource: string | File): Promise<OCRResult> {
        const worker = await this.getWorker();

        try {
            if (import.meta.env.DEV) {
                console.log('OCR: Processing image');
            }

            // If imageSource is a File, convert it to data URL
            const imageData = imageSource instanceof File 
                ? await this.fileToDataUrl(imageSource)
                : imageSource;

            const { data } = await worker.recognize(imageData);
            
            // Extract numbers from the text
            const numbers = data.text
                .split('\n')
                .join('')
                .match(/\d+/g)
                ?.map(Number) || [];

            // Get the longest number sequence as the likely meter reading
            const longestNumber = numbers.reduce((acc: number, curr: number) => 
                String(curr).length > String(acc).length ? curr : acc, 0);

            const result = {
                text: data.text,
                confidence: data.confidence,
                numbers: [longestNumber, ...numbers.filter((n: number) => n !== longestNumber)]
            };

            if (import.meta.env.DEV) {
                console.log('OCR: Processing complete', result);
            }

            return result;
        } catch (error) {
            console.error('OCR processing failed:', error);
            throw new Error('Failed to process image');
        }
    }

    private static async fileToDataUrl(file: File): Promise<string> {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result as string);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    public static async cleanup(): Promise<void> {
        if (this.worker) {
            await this.worker.terminate();
            this.worker = null;
        }
    }

    public static isReady(): boolean {
        return !!this.worker && !this.isInitializing;
    }
}

interface UseOCRResult {
    processImage: (image: string | File) => Promise<OCRResult | null>;
    isProcessing: boolean;
    error: string | null;
    isReady: boolean;
}

export function useOCR(): UseOCRResult {
    const [isProcessing, setIsProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const processImage = async (image: string | File): Promise<OCRResult | null> => {
        setIsProcessing(true);
        setError(null);

        try {
            const result = await OCRService.processImage(image);
            return result;
        } catch (err) {
            setError(err instanceof Error ? err.message : 'OCR processing failed');
            return null;
        } finally {
            setIsProcessing(false);
        }
    };

    useEffect(() => {
        return () => {
            OCRService.cleanup().catch(console.error);
        };
    }, []);

    return {
        processImage,
        isProcessing,
        error,
        isReady: OCRService.isReady()
    };
}