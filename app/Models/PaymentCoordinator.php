<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCoordinator extends Model
{
    protected $fillable = ['cashier_id', 'procurement_ids', 'total_price', 'status'];

    protected $casts = [
        'procurement_ids' => 'array',
    ];

    public function cashier()
    {
        return $this->belongsTo(Cashier::class);
    }

    public function procurements()
    {
        return Procurement::whereIn('id', $this->procurement_ids)->get();
    }

}
