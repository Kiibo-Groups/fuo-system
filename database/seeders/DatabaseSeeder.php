<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\{
    User,
    Branch,
    ChecklistTemplate,
    SparePart,
    Generator
};

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // 1. Sucursales
        $mty = Branch::create(['name' => 'Monterrey (Matriz)', 'location' => 'Santa Catarina, NL']);
        $cdmx = Branch::create(['name' => 'CDMX Norte', 'location' => 'Tlalnepantla, EdoMex']);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin15978'),
            'role' => 'admin',
            'remember_token' => Str::random(10),
            'current_team_id' => null,
            'profile_photo_path' => null,
            'branch_id' => $mty->id
        ]);

        User::create([
            'name' => 'Dueño CDMX',
            'email' => 'owner.cdmx@generadores.com',
            'password' => Hash::make('owner123'),
            'role' => 'owner',
            'branch_id' => $cdmx->id
        ]);

        User::create([
            'name' => 'Técnico Taller',
            'email' => 'tech@generadores.com',
            'password' => Hash::make('tech123'),
            'role' => 'technician',
            'branch_id' => $mty->id
        ]);

        // 3. Plantilla de Checklist inicial
        ChecklistTemplate::create([
            'title' => 'Revisión de Entrada Estándar',
            'items' => [
                'Nivel de aceite correcto',
                'Estado de la bujía',
                'Filtro de aire limpio',
                'Voltaje en carga (120v/240v)',
                'Fugas de combustible',
                'Estado del panel de control'
            ],
            'is_active' => true
        ]);

        // 4. Inventario inicial de refacciones
        SparePart::create(['name' => 'Bujía Champion', 'stock' => 50, 'unit_cost' => 85.00, 'low_stock_threshold' => 10]);
        SparePart::create(['name' => 'Filtro de Aire Universal', 'stock' => 30, 'unit_cost' => 150.00, 'low_stock_threshold' => 5]);
        SparePart::create(['name' => 'Carburador Genérico 5kW', 'stock' => 10, 'unit_cost' => 850.00, 'low_stock_threshold' => 2]);

        // 5. Un generador de prueba
        Generator::create([
            'model' => 'Champion 7500W Dual Fuel',
            'serial_number' => 'CH-99887766',
            'internal_folio' => 'FOL-2024-001',
            'cost' => 12500.00,
            'status' => 'Recibido en almacén',
            'current_branch_id' => $mty->id
        ]);

    }
}
