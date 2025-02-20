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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('reading_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_ht', 10, 2);
            $table->decimal('tva', 10, 2);
            $table->decimal('amount_ttc', 10, 2);
            $table->enum('status', ['draft', 'sent', 'paid'])->default('draft');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
