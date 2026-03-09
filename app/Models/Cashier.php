<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    protected $table = 'cashiers';

    protected $fillable = ['vehicle_id', 'due_day', 'amount','status','bank_name','account_number','account_name','rental_agreement_start_date','rental_agreement_end_date'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
