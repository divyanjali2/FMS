<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetDecision extends Model
{
    protected $fillable = [
        'garage_report_id',
        'inspection_id',
        'issue_id',
        'fault_id',
        'status',
        'type',
        'decision',
        'supplier_id',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT_TO_GARAGE = 'sent_to_garage';
    const STATUS_OWNER_REPAIR = 'owner_repair';

    public function garageReport()
    {
        return $this->belongsTo(GarageReport::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function fault()
    {
        return $this->belongsTo(Fault::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

}
