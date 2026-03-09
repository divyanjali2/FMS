<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GMWorkStatus extends Model
{
    use HasFactory;

    protected $table = 'gm_work_status';

    protected $fillable = [
        'inspection_id',
        'issue_inventory_id',
        'status',
    ];

    // Relationships
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function gmReview()
    {
        return $this->belongsTo(GMReview::class);
    }
    
    public function issueInventory()
    {
        return $this->belongsTo(IssueInventory::class, 'issue_inventory_id');
    }

    public function inbuildIssue()
    {
        return $this->hasOneThrough(
            GarageInbuildIssue::class,
            IssueInventory::class,
            'id',          
            'id',              
            'issue_inventory_id',
            'inbuild_issue_id'   
        );
    }
}
