<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $units = [
            ['code' => 'KG', 'symbol' => 'kg', 'name_en' => 'Kilogram', 'name_ar' => 'كيلوغرام', 'description_en' => 'Weight unit', 'description_ar' => 'وحدة الوزن'],
            ['code' => 'TON', 'symbol' => 'ton', 'name_en' => 'Ton', 'name_ar' => 'طن', 'description_en' => 'Weight unit', 'description_ar' => 'وحدة الوزن'],
            ['code' => 'BAG', 'symbol' => 'bag', 'name_en' => 'Bag', 'name_ar' => 'كيس', 'description_en' => 'Packaging unit', 'description_ar' => 'وحدة التعبئة'],
        ];

        $unit = $this->faker->randomElement($units);

        return [
            'code' => $unit['code'],
            'symbol' => $unit['symbol'],
            'name_en' => $unit['name_en'],
            'name_ar' => $unit['name_ar'],
            'name' => $unit['name_en'],
            'description_en' => $unit['description_en'],
            'description_ar' => $unit['description_ar'],
            'description' => $unit['description_en'],
        ];
    }
}

