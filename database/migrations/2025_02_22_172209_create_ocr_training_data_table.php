<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ocr_training_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_id')->constrained()->cascadeOnDelete();
            $table->string('original_text');  // Original OCR result
            $table->string('corrected_text'); // User corrected value
            $table->decimal('confidence', 5, 2); // Original OCR confidence
            $table->json('metadata')->nullable(); // Additional OCR data
            $table->string('image_url'); // URL to the meter image
            $table->boolean('verified')->default(false); // Whether this sample has been verified
            $table->timestamps();

            $table->index(['confidence']); // For querying low confidence results
            $table->index(['verified']); // For finding unverified samples
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_training_data');
    }
};