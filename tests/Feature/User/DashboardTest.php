<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار الوصول لصفحة الرئيسية للمستخدم
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'ev' => 1,
            'sv' => 1,
            'status' => 1,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get(route('user.home'));

        $response->assertStatus(200);
    }

    /**
     * اختبار أن المستخدم غير المسجل الدخول لا يستطيع الوصول للوحة التحكم
     */
    public function test_guest_cannot_access_user_dashboard(): void
    {
        $response = $this->get(route('user.home'));

        $response->assertRedirect(route('user.login'));
    }

    /**
     * اختبار أن المستخدم غير المؤكد لا يستطيع الوصول للوحة التحكم
     */
    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'ev' => 0, // email not verified
            'sv' => 1,
            'status' => 1,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get(route('user.home'));

        // قد يتم إعادة التوجيه لصفحة التأكيد
        $response->assertStatus(302);
    }
}

