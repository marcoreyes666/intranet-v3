<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body'); // acepta HTML simple/markdown
            $table->foreignId('author_id')->constrained('users');
            $table->enum('status', ['draft','published'])->default('draft');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            // Segmentación simple y escalable
            $table->enum('audience', ['all','role','department'])->default('all');
            $table->json('audience_values')->nullable(); // ej: ["Administrador","Rector"] o [3,5]
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('announcements');
    }
};
