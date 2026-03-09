<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleFreeze extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'start_date',
        'end_date',
        'reason',
        'remarks',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
