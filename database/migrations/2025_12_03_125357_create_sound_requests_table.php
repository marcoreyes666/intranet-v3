<?php

use App\Enums\SoundRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sound_requests', function (Blueprint $table) {
            $table->id();

            // Quién solicita
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Relación opcional con el evento del calendario
            // Ajusta 'events' si tu tabla se llama diferente
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();

            // Datos del evento
            $table->string('event_title')->nullable(); // por si no hay evento aún
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');

            // Requerimientos de sonido
            $table->text('requirements');

            // Estado usando el enum
            $table->string('status')
                ->default(SoundRequestStatus::Draft->value);

            // Marcar si es extemporánea (< 3 días)
            $table->boolean('is_late')->default(false);

            // Comentarios internos (de sistemas al devolver o rechazar)
            $table->text('review_comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sound_requests');
    }
};
