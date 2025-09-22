<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('events', function (Blueprint $table) {
            // Índices individuales para rangos por start/end
            $table->index('start');
            $table->index('end');
        });
    }

    public function down(): void {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['start']);
            $table->dropIndex(['end']);
        });
    }
};
