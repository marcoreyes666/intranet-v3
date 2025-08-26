<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Necesitas doctrine/dbal instalado para usar change()
            $table->enum('categoria', ['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])
                  ->nullable()
                  ->default(null)
                  ->change();

            $table->enum('prioridad', ['Baja','Media','Alta','Crítica'])
                  ->nullable()
                  ->default(null)
                  ->change();

            $table->enum('estado', ['Abierto','En proceso','Resuelto','Cerrado'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('categoria', ['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])
                  ->default('Sistemas')
                  ->nullable(false)
                  ->change();

            $table->enum('prioridad', ['Baja','Media','Alta','Crítica'])
                  ->default('Media')
                  ->nullable(false)
                  ->change();

            $table->enum('estado', ['Abierto','En proceso','Resuelto','Cerrado'])
                  ->default('Abierto')
                  ->nullable(false)
                  ->change();
        });
    }
};
