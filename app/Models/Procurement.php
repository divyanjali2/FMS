<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'issue_inventory_id',
        'status',
        'supplier_id',
        'price',
        'remark',
        'fulfilled_qty',
        'bill_path',
        'procurement_status',
        'po_id',
    ];

    // Relationships
    public function issueInventory()
    {
        return $this->belongsTo(IssueInventory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function accountantReview()
    {
        return $this->hasOne(AccountantReview::class, 'procurement_id');
    }

    public function grns()
    {
        return $this->hasMany(\App\Models\GRN::class);
    }

}
