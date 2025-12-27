<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Roles & Permissions';
        
        $query = Role::with('permissions');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $roles = $query->orderBy('id', 'desc')->paginate(getPaginate());
        
        return view('admin.role.index', compact('pageTitle', 'roles'));
    }

    public function create()
    {
        $pageTitle = 'Create Role';
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.role.create', compact('pageTitle', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        $this->audit('create', 'تم إنشاء دور جديد: ' . $role->name, $role);

        $notify[] = ['success', 'Role created successfully'];
        return redirect()->route('admin.role.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Role';
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.role.edit', compact('pageTitle', 'role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        $oldValues = $role->getOriginal();
        $role->permissions()->sync($request->permissions ?? []);
        $role->save();
        $newValues = $role->getChanges();

        $this->audit('update', 'تم تحديث الدور: ' . $role->name, $role, $oldValues, $newValues);

        $notify[] = ['success', 'Role updated successfully'];
        return redirect()->route('admin.role.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deleting super_admin role
        if ($role->slug === 'super_admin') {
            $notify[] = ['error', 'Cannot delete Super Admin role'];
            return back()->withNotify($notify);
        }
        
        $roleName = $role->name;
        $role->permissions()->detach();
        $role->admins()->detach();
        $role->delete();

        $this->audit('delete', 'تم حذف الدور: ' . $roleName, $role);

        $notify[] = ['success', 'Role deleted successfully'];
        return back()->withNotify($notify);
    }
}
