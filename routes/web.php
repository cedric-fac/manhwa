<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OcrTrainingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client Routes
    Route::resource('clients', ClientController::class);

    // Reading Routes
    Route::get('/clients/{client}/readings', [ReadingController::class, 'index'])->name('readings.index');
    Route::get('/clients/{client}/readings/create', [ReadingController::class, 'create'])->name('readings.create');
    Route::post('/clients/{client}/readings', [ReadingController::class, 'store'])->name('readings.store');
    Route::get('/clients/{client}/readings/{reading}', [ReadingController::class, 'show'])->name('readings.show');

    // Invoice Routes
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/readings/{reading}/invoice', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status.update');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
    });

    // OCR Training Routes (Admin Only)
    Route::middleware('ocr.admin')->group(function () {
        Route::get('/ocr/dashboard', [OcrTrainingController::class, 'index'])->name('ocr.dashboard');
        Route::get('/ocr/review/{trainingData}', [OcrTrainingController::class, 'review'])->name('ocr.review');
        Route::post('/ocr/update/{trainingData}', [OcrTrainingController::class, 'update'])->name('ocr.update');
        Route::post('/ocr/store/{reading}', [OcrTrainingController::class, 'store'])->name('ocr.store');
        Route::get('/ocr/statistics', [OcrTrainingController::class, 'statistics'])->name('ocr.statistics');
    });
});

require __DIR__.'/auth.php';
