<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Biblioteca de imágenes pre-cargadas, asociadas a folios de generadores.
     * Cuando se importa un Excel, si el folio ya tiene una imagen aquí,
     * se asigna automáticamente al generador creado.
     */
    public function up(): void
    {
        Schema::create('generator_images', function (Blueprint $table) {
            $table->id();
            $table->string('internal_folio')->index();      // Folio al que pertenece
            $table->string('file_path');                    // Ruta en storage
            $table->string('original_name');                // Nombre original del archivo
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('generator_id')->nullable()->constrained('generators')->onDelete('set null');
            $table->boolean('matched')->default(false);     // true = ya se asignó al generador
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generator_images');
    }
};
