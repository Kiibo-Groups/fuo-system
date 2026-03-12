<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
    protected $fillable = ['name', 'location', 'commission_rate'];

    /** Calcula el precio con comisión para un costo dado */
    public function calculateOwnerPrice(float $cost): float
    {
        return round($cost + ($cost * $this->commission_rate / 100), 2);
    }

    /** Calcula solo el monto de comisión para un costo dado */
    public function calculateCommissionAmount(float $cost): float
    {
        return round($cost * $this->commission_rate / 100, 2);
    }

    public function users() { return $this->hasMany(User::class); }
    /** Generadores cuya ubicación operativa actual es esta sucursal */
    public function generators() { return $this->hasMany(Generator::class, 'current_branch_id'); }
    /** Generadores asignados/destinados a esta sucursal (incluye los que están en proceso) */
    public function assignedGenerators() { return $this->hasMany(Generator::class, 'assigned_branch_id'); }
}
