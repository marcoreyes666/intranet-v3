<?php

// database/migrations/2025_08_29_000005_create_purchase_details_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_form_id')->unique()->constrained('request_forms')->cascadeOnDelete();
            $table->text('justification');
            $table->json('urls')->nullable();                      // ["https://amazon...","https://..."]
            $table->timestamp('delivered_at')->nullable();         // cuándo se entregó
            $table->foreignId('completed_by')->nullable()->constrained('users'); // quien marcó completado
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_details');
    }
};
