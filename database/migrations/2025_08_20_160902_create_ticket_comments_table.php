<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('comentario');
            $table->enum('visibility', ['publico', 'interno'])->default('publico');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('ticket_id', 'tc_ticket_id_idx');
            $table->index('user_id', 'tc_user_id_idx');
            $table->index('visibility', 'tc_visibility_idx');
            $table->index('created_at', 'tc_created_at_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ticket_comments');
    }
};
