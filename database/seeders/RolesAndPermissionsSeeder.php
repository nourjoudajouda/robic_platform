<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Full access to all features',
            'status' => 1
        ]);

        $warehousesTeam = Role::create([
            'name' => 'Warehouses Team',
            'slug' => 'warehouses_team',
            'description' => 'Access to warehouses and batches management',
            'status' => 1
        ]);

        $financeTeam = Role::create([
            'name' => 'Finance Team',
            'slug' => 'finance_team',
            'description' => 'Access to financial operations',
            'status' => 1
        ]);

        // Create Permissions Groups
        $permissions = [
            // Products
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'group' => 'products'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'group' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'group' => 'products'],

            // Warehouses
            ['name' => 'View Warehouses', 'slug' => 'warehouses.view', 'group' => 'warehouses'],
            ['name' => 'Create Warehouses', 'slug' => 'warehouses.create', 'group' => 'warehouses'],
            ['name' => 'Edit Warehouses', 'slug' => 'warehouses.edit', 'group' => 'warehouses'],
            ['name' => 'Delete Warehouses', 'slug' => 'warehouses.delete', 'group' => 'warehouses'],

            // Batches
            ['name' => 'View Batches', 'slug' => 'batches.view', 'group' => 'batches'],
            ['name' => 'Create Batches', 'slug' => 'batches.create', 'group' => 'batches'],
            ['name' => 'Edit Batches', 'slug' => 'batches.edit', 'group' => 'batches'],
            ['name' => 'Delete Batches', 'slug' => 'batches.delete', 'group' => 'batches'],

            // Users
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'Approve Establishments', 'slug' => 'users.approve_establishments', 'group' => 'users'],

            // Deposits
            ['name' => 'View Deposits', 'slug' => 'deposits.view', 'group' => 'deposits'],
            ['name' => 'Approve Deposits', 'slug' => 'deposits.approve', 'group' => 'deposits'],
            ['name' => 'Reject Deposits', 'slug' => 'deposits.reject', 'group' => 'deposits'],

            // Withdrawals
            ['name' => 'View Withdrawals', 'slug' => 'withdrawals.view', 'group' => 'withdrawals'],
            ['name' => 'Approve Withdrawals', 'slug' => 'withdrawals.approve', 'group' => 'withdrawals'],
            ['name' => 'Reject Withdrawals', 'slug' => 'withdrawals.reject', 'group' => 'withdrawals'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'reports'],

            // Settings
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'settings'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['slug']] = Permission::create($permission);
        }

        // Assign all permissions to Super Admin
        $superAdmin->permissions()->attach($createdPermissions);

        // Assign warehouses permissions to Warehouses Team
        $warehousesTeam->permissions()->attach([
            $createdPermissions['products.view']->id,
            $createdPermissions['products.create']->id,
            $createdPermissions['products.edit']->id,
            $createdPermissions['products.delete']->id,
            $createdPermissions['warehouses.view']->id,
            $createdPermissions['warehouses.create']->id,
            $createdPermissions['warehouses.edit']->id,
            $createdPermissions['warehouses.delete']->id,
            $createdPermissions['batches.view']->id,
            $createdPermissions['batches.create']->id,
            $createdPermissions['batches.edit']->id,
            $createdPermissions['batches.delete']->id,
        ]);

        // Assign finance permissions to Finance Team
        $financeTeam->permissions()->attach([
            $createdPermissions['users.view']->id,
            $createdPermissions['deposits.view']->id,
            $createdPermissions['deposits.approve']->id,
            $createdPermissions['deposits.reject']->id,
            $createdPermissions['withdrawals.view']->id,
            $createdPermissions['withdrawals.approve']->id,
            $createdPermissions['withdrawals.reject']->id,
            $createdPermissions['reports.view']->id,
        ]);
    }
}
