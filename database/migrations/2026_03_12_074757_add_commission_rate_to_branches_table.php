<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la tasa de comisión a la sucursal.
     * Ejemplo: 2.5 significa 2.5% sobre el costo real del generador.
     * Este porcentaje se suma al costo para calcular el precio que ve el owner.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)
                  ->default(0.00)
                  ->after('location')
                  ->comment('Porcentaje de comisión sobre el costo. Ej: 2.5 = 2.5%');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
