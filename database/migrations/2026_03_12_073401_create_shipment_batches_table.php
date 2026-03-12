<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un "Lote de Envío" agrupa múltiples generadores que salen juntos
     * en una misma tarima, con una sola guía y paquetería.
     */
    public function up(): void
    {
        Schema::create('shipment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('shipping_company');
            $table->string('tracking_number');
            $table->string('photo_evidence_path')->nullable();
            $table->json('evidences')->nullable();
            $table->string('notes')->nullable(); // Observaciones del lote
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_batches');
    }
};
