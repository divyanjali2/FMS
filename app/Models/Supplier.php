<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function categories()
    {
        return $this->belongsToMany(DefectCategory::class, 'category_supplier');
    }
}

