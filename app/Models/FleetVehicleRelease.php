<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetVehicleRelease extends Model
{
    protected $fillable = [
        'fleet_post_check_id',
        'status',
        'remarks'
    ];

    public function fleetPostCheck()
    {
        return $this->belongsTo(FleetPostCheck::class);
    }

    public function inspection()
    {
        return $this->fleetPostCheck->inspection();
    }

    public function issue()
    {
        return $this->fleetPostCheck->issue();
    }

    public function fault()
    {
        return $this->fleetPostCheck->fault();
    }
}

