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
        | 5. Plantillas de Checklist (Checklist Templates)
        |--------------------------------------------------------------------------
        */
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Ej: "Revisión General"
            $table->json('items'); // Almacenamos los puntos (bujía, gasolina, etc.) como JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
