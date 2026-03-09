<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $table = 'installments';

    protected $fillable = [
        'cashier_id',
        'procurement_ids',
        'payment_coordinator_id',
        'type',
        'options',
        'status'
    ];

    protected $casts = [
        'options' => 'array', 
        'procurement_ids' => 'array',
    ];

    public function cashier() {
        return $this->belongsTo(Cashier::class);
    }

    public function procurement() {
        return $this->belongsTo(Procurement::class);
    }

    public function paymentCoordinator() {
        return $this->belongsTo(\App\Models\PaymentCoordinator::class);
    }
}

