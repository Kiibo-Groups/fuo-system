<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopLog extends Model
{
    protected $fillable = ['generator_id', 'technician_id', 'diagnosis', 'total_repair_cost', 'completed_at', 'is_paid'];
    protected $casts = ['completed_at' => 'datetime'];
    public function spareParts() { 
        return $this->belongsToMany(SparePart::class, 'workshop_spare_part')->withPivot('quantity', 'cost_at_moment'); 
    }

    public function sparePartsLog() {
        return $this->hasMany(SparePartWorkshopLog::class, 'workshop_log_id');
    }
}
