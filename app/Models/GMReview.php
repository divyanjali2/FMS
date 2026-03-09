<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GMReview extends Model
{
    protected $table = 'gm_reviews';

    protected $fillable = [
        'inspection_id',
        'procurement_id',
        'status',
        'comments',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function garageReport()
    {
        return $this->hasOne(GarageReport::class, 'inspection_id', 'inspection_id');
    }

    public function mdReview()
    {
        return $this->hasOne(MDReview::class, 'gm_review_id');
    }

    public function dispatches()
{
    return $this->hasMany(\App\Models\GmDispatch::class, 'inspection_id', 'inspection_id');
}

}
