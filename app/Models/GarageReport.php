<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarageReport extends Model
{
    protected $fillable = ['inspection_id','issue_id','hours','notes','images','status'];

    protected $casts = [
        'images' => 'array',
        'status' => 'string',
    ];

    const STATUS_SENT_TO_FLEET = 'sent_to_fleet';
    const STATUS_SENT_TO_GARAGE = 'sent_to_garage';
    const STATUS_SENT_BACK_TO_GARAGE = 'sent_back_to_garage';

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id');
    }
}

