<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = ['name', 'category_id'];

    public function garageReports()
    {
        return $this->hasMany(GarageReport::class, 'issue_id');
    }

    public function category()
    {
        return $this->belongsTo(DefectCategory::class, 'category_id');
    }
}


