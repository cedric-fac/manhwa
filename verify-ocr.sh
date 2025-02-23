#!/bin/bash

echo "🔍 Verifying OCR Training System..."

# Clear application cache
echo "Clearing cache..."
php artisan optimize:clear

# Run migrations
echo "Running migrations..."
php artisan migrate:fresh --seed

# Run tests
echo "Running OCR tests..."
php artisan test tests/Feature/OcrTest.php --testdox

# Simulate OCR training
echo "Simulating OCR training..."
php artisan ocr:simulate --samples=3

# Generate performance report
echo "Generating performance report..."
php artisan ocr:report --days=7

# Check routes
echo "Verifying routes..."
php artisan route:list | grep ocr

# Check storage permissions
echo "Checking storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Verify Ziggy routes
echo "Verifying Ziggy routes..."
php artisan ziggy:generate

# Verify key components
echo "Verifying key components..."
declare -a components=(
    "app/Models/OcrTrainingData.php"
    "app/Http/Controllers/OcrTrainingController.php"
    "app/Support/OcrManager.php"
    "app/Providers/OcrServiceProvider.php"
    "config/ocr.php"
    "resources/js/Pages/Ocr/Dashboard.jsx"
    "resources/js/Pages/Ocr/Review.jsx"
    "resources/js/Pages/Ocr/Statistics.jsx"
    "resources/js/Layouts/OcrLayout.jsx"
)

for component in "${components[@]}"; do
    if [ -f "$component" ]; then
        echo "✅ Found: $component"
    else
        echo "❌ Missing: $component"
        exit 1
    fi
done

# Final status
echo "🎉 OCR Training System verification complete!"
echo "📝 Check OCR_CHECKLIST.md for implementation details"
echo "📘 Review DEPLOYMENT.md for production deployment steps"