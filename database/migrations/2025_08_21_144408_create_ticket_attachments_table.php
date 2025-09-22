<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('path');                 // storage/app/tickets/{id}/...
            $table->string('nombre_original');      // nombre de archivo subido
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->enum('visibility', ['publico', 'interno'])->default('publico');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('ticket_id', 'ta_ticket_id_idx');
            $table->index('user_id', 'ta_user_id_idx');
            $table->index('visibility', 'ta_visibility_idx');
            $table->index('created_at', 'ta_created_at_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ticket_attachments');
    }
};
