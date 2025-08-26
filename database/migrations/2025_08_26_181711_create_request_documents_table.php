<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                  ->constrained('requests')
                  ->cascadeOnDelete();
            $table->enum('doc_type', ['pdf','xlsx']);
            $table->string('template');  // nombre del archivo plantilla usado
            $table->string('path');      // ruta dentro de storage
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('request_documents');
    }
};
