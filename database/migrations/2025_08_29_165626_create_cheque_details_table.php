<?php

// database/migrations/2025_08_29_000004_create_cheque_details_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cheque_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_form_id')->unique()->constrained('request_forms')->cascadeOnDelete();
            $table->string('pay_to', 150);                         // A favor de
            $table->text('concept');                               // Concepto de pago
            $table->enum('currency', ['MXN','USD'])->default('MXN');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cheque_details');
    }
};
