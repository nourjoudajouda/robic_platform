<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert charge_limits data if not exists
        $chargeLimits = [
            [
                'id' => 1,
                'slug' => 'buy',
                'min_amount' => 10.00000000,
                'max_amount' => 10000.00000000,
                'fixed_charge' => 1.00000000,
                'percent_charge' => 1.00,
                'vat' => 2.50,
                'pickup_fee' => 0.00,
                'created_at' => null,
                'updated_at' => '2025-12-15 09:10:42',
            ],
            [
                'id' => 2,
                'slug' => 'sell',
                'min_amount' => 10.00000000,
                'max_amount' => 10000.00000000,
                'fixed_charge' => 2.00000000,
                'percent_charge' => 3.00,
                'vat' => 0.00,
                'pickup_fee' => 0.00,
                'created_at' => null,
                'updated_at' => '2024-12-11 06:04:43',
            ],
            [
                'id' => 3,
                'slug' => 'gift',
                'min_amount' => 10.00000000,
                'max_amount' => 10000.00000000,
                'fixed_charge' => 1.00000000,
                'percent_charge' => 2.00,
                'vat' => 0.00,
                'pickup_fee' => 0.00,
                'created_at' => null,
                'updated_at' => '2024-12-11 06:04:47',
            ],
            [
                'id' => 4,
                'slug' => 'redeem',
                'min_amount' => 10.00000000,
                'max_amount' => 10000.00000000,
                'fixed_charge' => 0.00000000,
                'percent_charge' => 0.00,
                'vat' => 0.00,
                'pickup_fee' => 15.00,
                'created_at' => null,
                'updated_at' => '2025-12-25 13:03:30',
            ],
        ];

        foreach ($chargeLimits as $chargeLimit) {
            DB::table('charge_limits')->updateOrInsert(
                ['id' => $chargeLimit['id']],
                $chargeLimit
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the data
        // DB::table('charge_limits')->whereIn('id', [1, 2, 3, 4])->delete();
    }
};
