<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OCR Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the OCR system.
    |
    */

    // Minimum confidence threshold for automatic acceptance
    'confidence_threshold' => env('OCR_CONFIDENCE_THRESHOLD', 80),

    // Maximum attempts for OCR processing
    'max_attempts' => env('OCR_MAX_ATTEMPTS', 3),

    // Delay between attempts (in seconds)
    'retry_delay' => env('OCR_RETRY_DELAY', 2),

    // Enable/disable OCR review notifications
    'enable_notifications' => env('OCR_ENABLE_NOTIFICATIONS', true),

    // Tesseract.js configuration
    'tesseract' => [
        'language' => env('OCR_LANGUAGE', 'fra'),
        'workerPath' => env('OCR_WORKER_PATH', '/js/worker.js'),
        'corePath' => env('OCR_CORE_PATH', '/js/tesseract-core.wasm.js'),
        'logger' => env('OCR_ENABLE_LOGGING', true),
    ],

    // Training data storage
    'storage' => [
        'disk' => env('OCR_STORAGE_DISK', 'local'),
        'path' => env('OCR_STORAGE_PATH', 'ocr/training'),
    ],

    // Performance monitoring
    'monitoring' => [
        'enabled' => env('OCR_MONITORING_ENABLED', true),
        'log_level' => env('OCR_LOG_LEVEL', 'info'),
        'metrics' => [
            'accuracy',
            'confidence',
            'processing_time',
            'review_time',
        ],
    ],

    // Review thresholds
    'review' => [
        'low_confidence' => env('OCR_LOW_CONFIDENCE_THRESHOLD', 75),
        'high_confidence' => env('OCR_HIGH_CONFIDENCE_THRESHOLD', 95),
        'auto_approve' => env('OCR_AUTO_APPROVE_THRESHOLD', 98),
    ],

    // Training settings
    'training' => [
        'enabled' => env('OCR_TRAINING_ENABLED', true),
        'min_samples' => env('OCR_MIN_TRAINING_SAMPLES', 100),
        'auto_training' => env('OCR_AUTO_TRAINING', false),
        'training_interval' => env('OCR_TRAINING_INTERVAL', 1000),
    ],

    // Image preprocessing
    'preprocessing' => [
        'enabled' => env('OCR_PREPROCESSING_ENABLED', true),
        'max_size' => env('OCR_MAX_IMAGE_SIZE', 2048),
        'operations' => [
            'grayscale' => true,
            'contrast' => true,
            'denoise' => true,
            'sharpen' => true,
        ],
    ],

    // Cache settings
    'cache' => [
        'enabled' => env('OCR_CACHE_ENABLED', true),
        'ttl' => env('OCR_CACHE_TTL', 3600),
        'prefix' => env('OCR_CACHE_PREFIX', 'ocr:'),
    ],
];