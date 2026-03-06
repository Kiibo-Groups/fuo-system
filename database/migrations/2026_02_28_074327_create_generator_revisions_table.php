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
        | 6. Revisiones y Taller (Revisions & Workshops)
        |--------------------------------------------------------------------------
        */
        Schema::create('generator_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users');
            $table->json('checklist_results'); // Guardamos qué marcó el técnico
            $table->text('observations')->nullable();
            $table->enum('result', ['Aprobada', 'No aprobada']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_revisions');
    }
};
