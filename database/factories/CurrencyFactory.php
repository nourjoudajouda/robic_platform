<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $currencies = [
            ['code' => 'SAR', 'symbol' => 'ر.س', 'name_en' => 'Saudi Riyal', 'name_ar' => 'ريال سعودي'],
            ['code' => 'USD', 'symbol' => '$', 'name_en' => 'US Dollar', 'name_ar' => 'دولار أمريكي'],
            ['code' => 'EUR', 'symbol' => '€', 'name_en' => 'Euro', 'name_ar' => 'يورو'],
        ];

        $currency = $this->faker->randomElement($currencies);

        return [
            'code' => $currency['code'],
            'symbol' => $currency['symbol'],
            'name_en' => $currency['name_en'],
            'name_ar' => $currency['name_ar'],
            'name' => $currency['name_en'],
        ];
    }
}

