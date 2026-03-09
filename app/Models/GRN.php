<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GRN extends Model
{

    protected $table = 'grns';

    protected $fillable = [
        'inspection_id', 'procurement_id', 'requested_qty', 'received_qty', 'remark'
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    // In App\Models\GRN.php
public function inspection()
{
    return $this->belongsToThrough(
        \App\Models\Inspection::class, 
        \App\Models\Procurement::class,
        'grn_id',   // foreign key on GRN table pointing to procurement
        'id',       // local key on inspection table
        'procurement_id', // local key on procurement table pointing to inspection
        'inspection_id'  // foreign key on inspection table
    );
}

public function accountantReview()
{
    return $this->hasOne(AccountantReview::class, 'procurement_id', 'procurement_id');
}


}

