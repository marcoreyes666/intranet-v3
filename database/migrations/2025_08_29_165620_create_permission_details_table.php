<?php

// database/migrations/2025_08_29_000003_create_permission_details_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_form_id')->unique()->constrained('request_forms')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable(); // hora de salida
            $table->time('end_time')->nullable();   // hora de regreso
            $table->string('reason', 200)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('permission_details');
    }
};
