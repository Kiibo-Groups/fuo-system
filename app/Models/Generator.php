<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Generator extends Model
{
    protected $fillable = ['model', 'serial_number', 'internal_folio', 'cost', 'sale_price', 'status', 'current_branch_id'];
    public function branch() { return $this->belongsTo(Branch::class, 'current_branch_id'); }
    public function revisions() { return $this->hasMany(GeneratorRevision::class); }
    public function workshopLogs() { return $this->hasMany(WorkshopLog::class); }
    public function statusHistory() { return $this->hasMany(GeneratorStatusHistory::class); }
    public function currentReservation() { 
        return $this->hasOne(Reservation::class)->where('is_active', true); 
    }
}
