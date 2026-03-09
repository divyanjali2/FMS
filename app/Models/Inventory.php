<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'item_code', 'name', 'description', 'available_quantity', 'remaining_quantity',
        'unit', 'purchase_date','supplier_id','min_stock_level','inventory_type_id',
    ];

    protected static function booted()
    {
        static::creating(function ($inventory) {
            // Set remaining_quantity equal to available_quantity when creating
            $inventory->remaining_quantity = $inventory->available_quantity;
        });

        static::saving(function ($inventory) {
            // If available_quantity is updated and remaining_quantity is 0 or null
            if ($inventory->isDirty('available_quantity') && 
                ($inventory->remaining_quantity === null || $inventory->remaining_quantity === 0)) {
                $inventory->remaining_quantity = $inventory->available_quantity;
            }
        });
    }

    public function initializeRemaining()
    {
        if ($this->remaining_quantity === 0 || $this->remaining_quantity === null) {
            $this->remaining_quantity = $this->available_quantity;
            $this->save();
        }
        return $this;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}