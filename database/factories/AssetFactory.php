<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomFloat(4, 10, 5000),
            'buy_price' => $this->faker->randomFloat(2, 50, 200),
        ];
    }
}

