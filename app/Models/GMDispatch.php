<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GMDispatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Optional if table name follows Laravel convention "g_m_dispatches")
     */
    protected $table = 'gm_dispatches';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'inspection_id',
        'user_id',
        'message',
        'status',
    ];

    /**
     * Relationships
     */

    // Each dispatch belongs to an inspection
    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    // Each dispatch is sent to a user (staff)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
