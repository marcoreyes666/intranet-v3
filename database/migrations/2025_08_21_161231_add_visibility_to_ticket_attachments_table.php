<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            // Enum con default 'publico'
            $table->enum('visibility', ['publico','interno'])
                  ->default('publico')
                  ->after('size');
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
