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
        | 7. Envíos y Separaciones (Logistics & Reservations)
        |--------------------------------------------------------------------------
        */
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained();
            $table->string('shipping_company'); // Ej: Castores
            $table->string('tracking_number');
            $table->string('photo_evidence_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
