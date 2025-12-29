<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * اختبار إنشاء مستخدم جديد
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);
    }

    /**
     * اختبار علاقة المستخدم مع المحافظ
     */
    public function test_user_has_many_wallets(): void
    {
        $user = User::factory()->create();
        
        Wallet::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->wallets);
        $this->assertInstanceOf(Wallet::class, $user->wallets->first());
    }

    /**
     * اختبار علاقة المستخدم مع المعاملات
     */
    public function test_user_has_many_transactions(): void
    {
        $user = User::factory()->create();
        
        Transaction::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, $user->transactions);
    }

    /**
     * اختبار علاقة المستخدم مع الأصول
     */
    public function test_user_has_many_assets(): void
    {
        $user = User::factory()->create();
        
        Asset::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->assets);
    }

    /**
     * اختبار scope للمستخدمين النشطين
     */
    public function test_scope_active_filters_active_users(): void
    {
        User::factory()->create(['status' => 1, 'ev' => 1, 'sv' => 1]);
        User::factory()->create(['status' => 0, 'ev' => 1, 'sv' => 1]);
        User::factory()->create(['status' => 1, 'ev' => 0, 'sv' => 1]);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
    }

    /**
     * اختبار خاصية fullname
     */
    public function test_user_has_fullname_attribute(): void
    {
        $user = User::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->fullname);
    }

    /**
     * اختبار خاصية mobileNumber
     */
    public function test_user_has_mobile_number_attribute(): void
    {
        $user = User::factory()->create([
            'dial_code' => '+966',
            'mobile' => '501234567',
        ]);

        $this->assertEquals('+966501234567', $user->mobileNumber);
    }

    /**
     * اختبار scope للمستخدمين المحظورين
     */
    public function test_scope_banned_filters_banned_users(): void
    {
        User::factory()->create(['status' => 1]);
        User::factory()->create(['status' => 0]);

        $bannedUsers = User::banned()->get();

        $this->assertCount(1, $bannedUsers);
    }
}

