<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_title',
        'report_date',
        'report_month',
        'report_week',
        'uploaded_by',
        'user_id',
        'remark',
        'file_path',
        'accepted',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'report_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The uploader
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}

