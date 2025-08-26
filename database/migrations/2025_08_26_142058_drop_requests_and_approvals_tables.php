<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('requests');
    }

    public function down(): void
    {
        // (opcional) recrear si quisieras, pero no es necesario ahora
    }
};
