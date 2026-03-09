<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountantReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'procurement_id',
        'status',
        'types',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

        public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function issue() {
        return $this->belongsTo(Issue::class);
    }

    public function fault() {
        return $this->belongsTo(Fault::class);
    }
}
