<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->string('client_name');
            $table->string('client_phone');
            $table->timestamp('expires_at'); // Para la lógica de las 4 horas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
