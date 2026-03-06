<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePartWorkshopLog extends Model
{
    protected $table = 'workshop_spare_part';
    protected $fillable = ['workshop_log_id', 'spare_part_id', 'quantity', 'cost_at_moment'];

    public function sparePart() {
        return $this->belongsTo(SparePart::class);
    }
}
