<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_name',
        'arrival_date',
        'departure_date',
        'passengers',
        'status',
        'notes',
        'company_id',
        'repair_type',
        'reference_no',
        'emer_booking_number',
        'emer_customer_name',
        'emer_no_of_passengers',
        'emer_arrival_date',
        'emer_departure_date',
        'deleted_by',
        'vehicle_pickup',
        'booking_number',
        'salutation',
        'alternative_start_date',
        'change_reason',
        'is_old_vehicle',
        'created_by'
    ];

    protected $casts = [
        'emer_arrival_date' => 'datetime',
        'emer_departure_date' => 'datetime',
        'arrival_date'   => 'datetime',
        'departure_date' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted()
    {
        static::deleting(function ($vehicle) {
            if (auth()->check()) {
                $vehicle->deleted_by = auth()->id();
                $vehicle->save();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function inspections()
    {
        return $this->hasMany(\App\Models\Inspection::class);
    }

    public function creator()
    {
        return $this->hasOne(\Spatie\Activitylog\Models\Activity::class, 'subject_id')
            ->where('subject_type', self::class)
            ->orderBy('id', 'asc'); 
    }

    public function creatorName()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
