<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_attachments', 'original_name')) {
                $table->renameColumn('original_name', 'nombre_original');
            }
            if (Schema::hasColumn('ticket_attachments', 'mime')) {
                $table->renameColumn('mime', 'mime_type');
            }
        });
    }
    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_attachments', 'nombre_original')) {
                $table->renameColumn('nombre_original', 'original_name');
            }
            if (Schema::hasColumn('ticket_attachments', 'mime_type')) {
                $table->renameColumn('mime_type', 'mime');
            }
        });
    }
};
