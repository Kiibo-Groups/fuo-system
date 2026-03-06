<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'generator_id', 
        'shipping_company', 
        'tracking_number', 
        'photo_evidence_path'
    ];

    public function generator() {
        return $this->belongsTo(Generator::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

}
