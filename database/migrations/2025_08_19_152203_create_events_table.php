<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('location')->nullable();   // 👈 NUEVO
        $table->dateTime('start')->index();
        $table->dateTime('end')->nullable()->index();
        $table->boolean('all_day')->default(false);
        $table->string('color')->nullable();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->timestamps();
    });
    }

    public function down(): void {
        Schema::dropIfExists('events');
    }
};
