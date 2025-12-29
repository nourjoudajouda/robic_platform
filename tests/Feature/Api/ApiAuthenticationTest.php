<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار تسجيل الدخول عبر API
     */
    public function test_user_can_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => bcrypt('password123'),
            'ev' => 1,
            'sv' => 1,
            'status' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'api@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user',
                ],
            ]);
    }

    /**
     * اختبار فشل تسجيل الدخول عبر API ببيانات خاطئة
     */
    public function test_user_cannot_login_via_api_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * اختبار الوصول للموارد المحمية عبر API
     */
    public function test_authenticated_user_can_access_protected_api_endpoints(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    /**
     * اختبار أن المستخدم غير المسجل الدخول لا يستطيع الوصول للموارد المحمية
     */
    public function test_unauthenticated_user_cannot_access_protected_api_endpoints(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}

