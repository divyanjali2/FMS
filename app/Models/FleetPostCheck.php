<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetPostCheck extends Model
{
    protected $fillable = [
        'inspection_id',
        'gm_work_status_id',
        'issue_id',
        'fault_id',
        'verified',
        'remarks',
        'status',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function gmWorkStatus()
    {
        return $this->belongsTo(GMWorkStatus::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function fault()
    {
        return $this->belongsTo(Fault::class);
    }

       public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
