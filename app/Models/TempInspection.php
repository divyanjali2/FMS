<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'fault_id',
        'status',
        'type',
        'job_status',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function fault()
    {
        return $this->belongsTo(Fault::class);
    }
}
