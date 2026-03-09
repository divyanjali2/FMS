<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarageInbuildIssue extends Model
{
    protected $table = 'garage_inbuild_issues';
    protected $fillable = ['garage_report_id', 'issue_id', 'fault_id', 'type'];

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id');
    }

    public function fault()
    {
        return $this->belongsTo(Fault::class, 'fault_id');
    }
}
