<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el precio que verá el owner (costo + comisión calculada).
     * Se guarda en BD para no recalcular en cada consulta.
     * Se actualiza automáticamente cuando se asigna/reasigna la sucursal.
     *
     * owner_price = cost + (cost * commission_rate / 100)
     */
    public function up(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->decimal('owner_price', 10, 2)
                  ->nullable()
                  ->after('cost')
                  ->comment('Precio que ve el owner = costo + comisión de la sucursal asignada');

            $table->decimal('commission_amount', 10, 2)
                  ->nullable()
                  ->after('owner_price')
                  ->comment('Monto en pesos de la comisión aplicada');
        });
    }

    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropColumn(['owner_price', 'commission_amount']);
        });
    }
};
