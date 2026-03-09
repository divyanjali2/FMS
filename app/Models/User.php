<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'is_sr',
        'is_elite',
        'department',
        'position',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_sr' => 'boolean',
        'is_elite' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

      /** check if user has role (string or array) */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return $this->roles()->whereIn('name', $role)->exists();
        }
        return $this->roles()->where('name', $role)->exists();
    }

    /** check permission by role mapping */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->all_permissions);
    }

    public function assignRole($role) {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function getAllPermissionsAttribute()
    {
        $rolePermissions = $this->roles()->with('permissions')->get()
                                ->pluck('permissions')
                                ->flatten()
                                ->pluck('name')
                                ->unique()
                                ->toArray();

        $directPermissions = $this->permissions()->pluck('name')->toArray();

        return array_unique(array_merge($rolePermissions, $directPermissions));
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
