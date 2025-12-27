<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManageAdminController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Manage Admins';
        
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
        
        return view('admin.admin.index', compact('pageTitle', 'admins'));
    }

    public function create()
    {
        $pageTitle = 'Add Admin';
        $roles = Role::where('status', 1)->orderBy('name')->get();
        return view('admin.admin.create', compact('pageTitle', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $admin->roles()->attach($request->roles);
        }

        $this->audit('create', 'تم إنشاء أدمن جديد: ' . $admin->username, $admin);

        $notify[] = ['success', 'Admin created successfully'];
        return redirect()->route('admin.admin.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Admin';
        $admin = Admin::with('roles')->findOrFail($id);
        $roles = Role::where('status', 1)->orderBy('name')->get();
        $adminRoles = $admin->roles->pluck('id')->toArray();
        
        return view('admin.admin.edit', compact('pageTitle', 'admin', 'roles', 'adminRoles'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'username' => 'required|string|max:255|unique:admins,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin->name = $request->name;
        $admin->email = strtolower($request->email);
        $admin->username = $request->username;
        
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }
        
        $admin->save();

        $oldValues = $admin->getOriginal();
        $admin->roles()->sync($request->roles ?? []);
        $admin->save();
        $newValues = $admin->getChanges();

        $this->audit('update', 'تم تحديث بيانات الأدمن: ' . $admin->username, $admin, $oldValues, $newValues);

        $notify[] = ['success', 'Admin updated successfully'];
        return redirect()->route('admin.admin.index')->withNotify($notify);
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent deleting yourself
        if ($admin->id == auth()->guard('admin')->id()) {
            $notify[] = ['error', 'You cannot delete your own account'];
            return back()->withNotify($notify);
        }
        
        $adminName = $admin->username;
        $admin->roles()->detach();
        $admin->delete();

        $this->audit('delete', 'تم حذف الأدمن: ' . $adminName, $admin);

        $notify[] = ['success', 'Admin deleted successfully'];
        return back()->withNotify($notify);
    }
}
