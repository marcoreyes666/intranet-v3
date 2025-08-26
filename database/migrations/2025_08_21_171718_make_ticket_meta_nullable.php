<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Si eran string/enum con default, los pasamos a NULL
            $table->string('categoria')->nullable()->default(null)->change();
            $table->string('prioridad')->nullable()->default(null)->change();
            $table->string('estado')->nullable()->default(null)->change(); // si antes era enum, cámbialo a string nullable en una migración previa
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Revertir (ajusta defaults a los que tenías antes)
            $table->string('categoria')->nullable(false)->default('Sistemas')->change();
            $table->string('prioridad')->nullable(false)->default('Media')->change();
            $table->string('estado')->nullable(false)->default('Abierto')->change();
        });
    }
};
