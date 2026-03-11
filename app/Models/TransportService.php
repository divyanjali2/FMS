<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'vehicle_id',
        'assigned_start_at',
        'pickup_location',
        'dropoff_location',
        'assigned_end_at',
        'chauffer_id',
        'passenger_count',
        'trip_code',
        'delete_note',
        'deleted_by',
        'deleted_at',
        'vehicle_type_id',
        'is_vehicle_assigned',
    ];

    protected $casts = [
        'assigned_start_at' => 'datetime',
        'assigned_end_at' => 'datetime',
        'passenger_count' => 'integer',
        'is_vehicle_assigned' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function chauffer(): BelongsTo
    {
        return $this->belongsTo(Chauffer::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}
