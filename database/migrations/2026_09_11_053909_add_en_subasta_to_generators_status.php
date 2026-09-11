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
        // Altering ENUM in MySQL
        DB::statement("ALTER TABLE generators MODIFY COLUMN status ENUM('Pedido en tránsito', 'Recibido en almacén', 'En revisión', 'En taller', 'Lista para envío', 'Enviado', 'Recibido en sucursal', 'Disponible', 'Separado', 'Vendido', 'No Procesado', 'En subasta') DEFAULT 'Pedido en tránsito'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE generators MODIFY COLUMN status ENUM('Pedido en tránsito', 'Recibido en almacén', 'En revisión', 'En taller', 'Lista para envío', 'Enviado', 'Recibido en sucursal', 'Disponible', 'Separado', 'Vendido', 'No Procesado') DEFAULT 'Pedido en tránsito'");
    }
};
