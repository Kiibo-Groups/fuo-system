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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($generator) {
            // Delete related histories and revisions
            $generator->revisions()->delete();
            $generator->statusHistory()->delete();
            
            // Delete related workshop logs and pivot table
            foreach ($generator->workshopLogs as $log) {
                // Remove relation from pivot table
                $log->spareParts()->detach();
                $log->delete();
            }

            // Delete shipments
            \App\Models\Shipment::where('generator_id', $generator->id)->delete();
            
            // Delete all reservations associated
            \App\Models\Reservation::where('generator_id', $generator->id)->delete();
            
            // Delete related sales if it is registered as polymorphic sellable
            \App\Models\SaleItem::where('sellable_type', static::class)
                ->where('sellable_id', $generator->id)
                ->delete();
        });
    }
}
