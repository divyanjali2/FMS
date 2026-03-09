<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnershipType extends Model
{
    use HasFactory;

    protected $table = 'ownership_types';

    protected $fillable = [
        'ownership_name',
    ];

    public $timestamps = true;
}
