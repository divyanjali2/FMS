<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueInventory extends Model
{
    protected $fillable = [
        'garage_report_id',
        'inbuild_issue_id',
        'inventory_id',
        'quantity'
    ];

    public function garageReport() {
        return $this->belongsTo(GarageReport::class);
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function garageInbuildIssue()
    {
        return $this->belongsTo(\App\Models\GarageInbuildIssue::class, 'inbuild_issue_id');
    }

    public function procurement()
    {
        return $this->hasOne(Procurement::class, 'issue_inventory_id');
    }


    public function garageIssues()
    {
        return $this->hasManyThrough(
            \App\Models\GarageInbuildIssue::class,  // Target
            \App\Models\GarageReport::class,        // Through
            'inspection_id',                        // GarageReport -> Inspection
            'garage_report_id',                      // GarageInbuildIssue -> GarageReport
            'garage_report_id',                      // IssueInventory local key
            'id'                                     // GarageReport local key
        )->with(['issue', 'fault']);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }



}
