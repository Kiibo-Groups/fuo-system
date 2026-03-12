<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega el campo assigned_branch_id a la tabla generators.
     * Este campo representa la SUCURSAL DESTINO/DUEÑA del generador,
     * separado del campo current_branch_id que indica la ubicación
     * operativa actual (almacén, taller, en tránsito, etc.).
     *
     * La sucursal asignada SIEMPRE puede ver el generador en su inventario,
     * sin importar el estado operativo del mismo.
     */
    public function up(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->foreignId('assigned_branch_id')
                  ->nullable()
                  ->after('current_branch_id')
                  ->constrained('branches')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropForeign(['assigned_branch_id']);
            $table->dropColumn('assigned_branch_id');
        });
    }
};
