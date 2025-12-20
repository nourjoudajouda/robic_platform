<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use GlobalStatus;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_roles');
    }

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }
        return $this->permissions()->where('id', $permission->id)->exists();
    }
}
