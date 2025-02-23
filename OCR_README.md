# OCR Training System Documentation

## Overview
The OCR Training System is a comprehensive solution for managing meter reading OCR processing, training data collection, and admin review workflows.

## Core Components

### 1. Models
- `Reading`: Handles meter reading data with OCR metadata
- `OcrTrainingData`: Manages OCR training datasets
- `User`: Includes admin permissions for OCR review

### 2. Middleware
- `EnsureIsOcrAdmin`: Controls access to OCR admin features
- `HandleInertiaRequests`: Manages Inertia.js integration

### 3. Controllers
- `OcrTrainingController`: Manages OCR review and training
- `ReadingController`: Handles meter reading processing

### 4. Services
- `OcrManager`: Core OCR processing logic
- `CloudinaryAdapter`: File storage integration

## Features

### OCR Processing
```php
// Process new reading
$reading = Reading::create([
    'value' => $request->value,
    'read_at' => $request->read_at
]);

// Add OCR data
$reading->addOcrMetadata([
    'confidence' => $confidence,
    'text' => $detectedText,
    'suggestions' => $suggestions
]);
```

### Training Data Collection
```php
// Store training data
OcrTrainingData::create([
    'reading_id' => $reading->id,
    'original_text' => $text,
    'confidence' => $confidence,
    'metadata' => $metadata
]);
```

### Admin Review
```php
// Review OCR data
$trainingData->update([
    'corrected_text' => $request->corrected_text,
    'verified' => true
]);
```

## Configuration

### Environment Variables
```env
OCR_CONFIDENCE_THRESHOLD=80
OCR_ENABLE_NOTIFICATIONS=true
OCR_STORAGE_DISK=cloudinary
```

### Routes
```php
Route::middleware(['auth', 'ocr.admin'])->group(function () {
    Route::get('/ocr/dashboard', [OcrTrainingController::class, 'index']);
    Route::get('/ocr/review/{trainingData}', [OcrTrainingController::class, 'review']);
    Route::post('/ocr/update/{trainingData}', [OcrTrainingController::class, 'update']);
});
```

## Testing

### Run Tests
```bash
php artisan test tests/Feature/OcrTest.php
```

### Simulation
```bash
php artisan ocr:simulate --samples=10
```

### Performance Report
```bash
php artisan ocr:report --days=7
```

## Security

1. Admin Access Control
   - Middleware protection
   - Role-based authorization
   - Secure routes

2. File Handling
   - Secure file uploads
   - Cloudinary integration
   - Type validation

3. Data Protection
   - Input validation
   - CSRF protection
   - Secure storage

## Maintenance

### Regular Tasks
1. Monitor OCR performance
2. Review training data
3. Update dependencies
4. Backup data
5. Check error logs

### Commands
```bash
# Verify system
./verify-ocr.sh

# Clear cache
php artisan optimize:clear

# Update routes
php artisan route:clear
```

## Monitoring

### Key Metrics
- OCR confidence levels
- Review completion rates
- Processing times
- Error rates
- Storage usage

### Performance Tools
1. Built-in reporting
2. Laravel logging
3. Queue monitoring
4. Storage analytics

## Support

### Troubleshooting
1. Check error logs
2. Verify configurations
3. Test file permissions
4. Monitor queues

### Contact
- Technical support: tech@example.com
- Admin support: admin@example.com

## Future Improvements
1. Machine learning integration
2. Batch processing
3. Mobile optimization
4. API expansion

## License
This OCR Training System is proprietary software. All rights reserved.