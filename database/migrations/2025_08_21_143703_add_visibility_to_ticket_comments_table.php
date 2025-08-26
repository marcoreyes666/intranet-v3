<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->enum('visibility', ['publico','interno'])->default('publico')->after('comentario');
            $table->index('visibility');
        });
    }
    public function down(): void {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
