<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'image'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles');
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('slug', $role)->exists();
        }
        return $this->roles()->where('id', $role->id)->exists();
    }

    public function hasPermission($permission)
    {
        // Super Admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Check if admin has the permission through any of their roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(array $roles)
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
}
