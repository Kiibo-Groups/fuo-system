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
        | 3. Generadores (Generators)
        |--------------------------------------------------------------------------
        */
        Schema::create('generators', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('serial_number')->unique();
            $table->string('internal_folio')->unique();
            $table->decimal('cost', 12, 2)->default(0);

            // Estados definidos en el punto 6 del alcance
            $table->enum('status', [
                'Pedido en tránsito',
                'Recibido en almacén',
                'En revisión',
                'En taller',
                'Lista para envío',
                'Enviado',
                'Recibido en sucursal',
                'Disponible',
                'Separado',
                'Vendido'
            ])->default('Pedido en tránsito');

            $table->foreignId('current_branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generators');
    }
};
