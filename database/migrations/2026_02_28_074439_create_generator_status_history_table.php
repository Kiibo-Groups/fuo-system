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
       
        /*
        |--------------------------------------------------------------------------
        | 8. Historial de Estados (Trazabilidad Total)
        |--------------------------------------------------------------------------
        */
        Schema::create('generator_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Quién hizo el cambio
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_status_history');
    }
};
