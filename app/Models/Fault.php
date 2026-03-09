<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fault extends Model
{
    protected $fillable = ['name', 'type', 'category_id'];

    public function inspections() {
        return $this->belongsToMany(Inspection::class, 'inspection_faults');
    }

    public function category()
    {
        return $this->belongsTo(DefectCategory::class, 'category_id');
    }
}