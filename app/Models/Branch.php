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
    protected $fillable = ['name', 'location'];
    public function users() { return $this->hasMany(User::class); }
    public function generators() { return $this->hasMany(Generator::class, 'current_branch_id'); }
}
