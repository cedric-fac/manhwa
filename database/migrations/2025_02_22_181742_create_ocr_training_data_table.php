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
            $table->string('original_text');
            $table->string('corrected_text')->nullable();
            $table->decimal('confidence', 5, 2);
            $table->json('metadata')->nullable();
            $table->string('image_url');
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->index(['confidence']);
            $table->index(['verified']);
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