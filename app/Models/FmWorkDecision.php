<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FmWorkDecision extends Model
{
    protected $fillable = ['inspection_id', 'issue_inventory_id', 'status'];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function issueInventory()
    {
        return $this->belongsTo(IssueInventory::class);
    }
}
