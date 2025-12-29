<?php

namespace Database\Factories;

use App\Models\MarketPriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketPriceHistoryFactory extends Factory
{
    protected $model = MarketPriceHistory::class;

    public function definition(): array
    {
        return [
            'market_price' => $this->faker->randomFloat(2, 50, 200),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

