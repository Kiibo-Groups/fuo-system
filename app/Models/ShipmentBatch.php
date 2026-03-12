<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentBatch extends Model
{
    protected $fillable = [
        'created_by',
        'shipping_company',
        'tracking_number',
        'photo_evidence_path',
        'evidences',
        'notes',
    ];

    protected $casts = [
        'evidences' => 'array',
    ];

    /** Los registros individuales de envío dentro de este lote */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /** Los generadores que forman parte de este lote (a través de shipments) */
    public function generators()
    {
        return $this->hasManyThrough(Generator::class, Shipment::class, 'shipment_batch_id', 'id', 'id', 'generator_id');
    }

    /** El usuario que creó el lote */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Cuántas unidades tiene el lote */
    public function getUnitCountAttribute(): int
    {
        return $this->shipments()->count();
    }
}
