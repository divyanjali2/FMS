<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MDReview extends Model
{
    protected $table = 'md_reviews';
    
    protected $fillable = [
        'inspection_id',
        'procurement_id',
        'gm_review_id',
        'md_comment',
        'status',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function gmReview()
    {
        return $this->belongsTo(GMReview::class);
    }
}
