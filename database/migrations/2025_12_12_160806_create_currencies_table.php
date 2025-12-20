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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('symbol');
            $table->timestamps();
        });

        // إضافة العملات الشائعة
        $currencies = [
            ['name' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => 'ر.س'],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£'],
            ['name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ'],
            ['name' => 'Kuwaiti Dinar', 'code' => 'KWD', 'symbol' => 'د.ك'],
            ['name' => 'Jordanian Dinar', 'code' => 'JOD', 'symbol' => 'د.ا'],
            ['name' => 'Egyptian Pound', 'code' => 'EGP', 'symbol' => 'ج.م'],
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->insert([
                'name' => $currency['name'],
                'code' => $currency['code'],
                'symbol' => $currency['symbol'],
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
        Schema::dropIfExists('currencies');
    }
};
