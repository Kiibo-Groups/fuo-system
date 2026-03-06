<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{

    protected $fillable = ['name', 'stock', 'unit_cost', 'low_stock_threshold'];
    public function workshopLogs() { 
        return $this->belongsToMany(WorkshopLog::class)->withPivot('quantity', 'cost_at_moment'); 
    }

    
}
