<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Assign Roles to Admins';
        
        $query = Admin::with('roles');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        $admins = $query->orderBy('id', 'desc')->paginate(getPaginate());
        $roles = Role::where('status', 1)->orderBy('name')->get();
        
        return view('admin.admin-role.index', compact('pageTitle', 'admins', 'roles'));
    }

    public function assign(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin->roles()->sync($request->roles ?? []);

        $notify[] = ['success', 'Roles assigned successfully'];
        return back()->withNotify($notify);
    }
}
