<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DefectCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'category_supplier');
    }

    public function faults()
    {
        return $this->hasMany(Fault::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'category_id');
    }

}
