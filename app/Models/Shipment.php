<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'generator_id',
        'shipment_batch_id',
        'shipping_company',
        'tracking_number',
        'photo_evidence_path',
        'evidences'
    ];

    protected $casts = [
        'evidences' => 'array',
    ];

    public function generator() {
        return $this->belongsTo(Generator::class);
    }

    public function batch() {
        return $this->belongsTo(ShipmentBatch::class, 'shipment_batch_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

}
