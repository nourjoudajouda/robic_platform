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
        // إضافة وحدات التعبئة
        $units = [
            ['name' => 'Bag', 'code' => 'BAG', 'symbol' => 'كيس', 'description' => 'كيس'],
            ['name' => 'Carton', 'code' => 'CARTON', 'symbol' => 'كرتونة', 'description' => 'كرتونة'],
        ];

        foreach ($units as $unit) {
            // التحقق من عدم وجود الوحدة مسبقاً
            $exists = DB::table('units')->where('code', $unit['code'])->exists();
            if (!$exists) {
                DB::table('units')->insert([
                    'name' => $unit['name'],
                    'code' => $unit['code'],
                    'symbol' => $unit['symbol'],
                    'description' => $unit['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الوحدات المضافة
        DB::table('units')->whereIn('code', ['BAG', 'CARTON'])->delete();
    }
};
