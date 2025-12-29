<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار تسجيل دخول المستخدم بنجاح
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'ev' => 1, // email verified
            'sv' => 1, // mobile verified
            'status' => 1, // active
        ]);

        $response = $this->post('/login', [
            'username' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('user.home'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    /**
     * اختبار فشل تسجيل الدخول ببيانات غير صحيحة
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('web');
    }

    /**
     * اختبار تسجيل خروج المستخدم
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->get(route('user.logout'));

        $response->assertRedirect(route('user.login'));
        $this->assertGuest('web');
    }

    /**
     * اختبار التسجيل كعضو جديد
     */
    public function test_user_can_register(): void
    {
        $userData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'dial_code' => '+966',
            'mobile' => '501234567',
            'country_code' => 'SA',
            'country_name' => 'Saudi Arabia',
        ];

        $response = $this->post(route('user.register'), $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $response->assertRedirect();
    }

    /**
     * اختبار أن المستخدم المعطل لا يستطيع تسجيل الدخول
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'status' => 0, // inactive
            'ev' => 1,
            'sv' => 1,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('web');
    }
}

