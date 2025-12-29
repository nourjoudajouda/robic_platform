<?php

namespace Tests\Unit\Models;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء مدير جديد
     */
    public function test_admin_can_be_created(): void
    {
        $admin = Admin::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->assertDatabaseHas('admins', [
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * اختبار علاقة المدير مع الأدوار
     */
    public function test_admin_belongs_to_many_roles(): void
    {
        $admin = Admin::factory()->create();
        $role1 = Role::factory()->create(['slug' => 'manager']);
        $role2 = Role::factory()->create(['slug' => 'editor']);

        $admin->roles()->attach([$role1->id, $role2->id]);

        $this->assertCount(2, $admin->roles);
    }

    /**
     * اختبار طريقة hasRole
     */
    public function test_admin_has_role_method(): void
    {
        $admin = Admin::factory()->create();
        $role = Role::factory()->create(['slug' => 'manager']);

        $admin->roles()->attach($role->id);

        $this->assertTrue($admin->hasRole('manager'));
        $this->assertTrue($admin->hasRole($role));
        $this->assertFalse($admin->hasRole('nonexistent'));
    }

    /**
     * اختبار طريقة hasAnyRole
     */
    public function test_admin_has_any_role_method(): void
    {
        $admin = Admin::factory()->create();
        $role1 = Role::factory()->create(['slug' => 'manager']);
        $role2 = Role::factory()->create(['slug' => 'editor']);

        $admin->roles()->attach($role1->id);

        $this->assertTrue($admin->hasAnyRole(['manager', 'editor']));
        $this->assertFalse($admin->hasAnyRole(['admin', 'supervisor']));
    }
}

