<?php

// database/migrations/2025_08_29_000006_create_purchase_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_detail_id')->constrained('purchase_details')->cascadeOnDelete();
            $table->decimal('qty', 10, 2);
            $table->string('unit', 30);            // pieza, caja, docena, etc.
            $table->string('description', 255);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_items');
    }
};
