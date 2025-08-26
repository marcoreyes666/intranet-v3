<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('path');          // storage path
            $table->string('original_name'); // nombre original
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ticket_attachments');
    }
};
