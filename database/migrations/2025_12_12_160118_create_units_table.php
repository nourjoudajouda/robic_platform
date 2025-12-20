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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('symbol');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // إضافة الوحدات الشائعة للوزن
        $units = [
            ['name' => 'Kilogram', 'code' => 'KG', 'symbol' => 'kg', 'description' => 'كيلوغرام'],
            ['name' => 'Gram', 'code' => 'G', 'symbol' => 'g', 'description' => 'غرام'],
            ['name' => 'Ton', 'code' => 'TON', 'symbol' => 'ton', 'description' => 'طن'],
            ['name' => 'Pound', 'code' => 'LB', 'symbol' => 'lb', 'description' => 'رطل'],
            ['name' => 'Ounce', 'code' => 'OZ', 'symbol' => 'oz', 'description' => 'أونصة'],
        ];

        foreach ($units as $unit) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
