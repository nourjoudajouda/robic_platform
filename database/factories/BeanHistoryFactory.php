<?php

namespace Database\Factories;

use App\Models\BeanHistory;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeanHistoryFactory extends Factory
{
    protected $model = BeanHistory::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            Status::BUY_HISTORY,
            Status::SELL_HISTORY,
        ]);

        $quantity = $this->faker->randomFloat(4, 1, 1000);
        $price = $this->faker->randomFloat(2, 50, 200);
        $amount = $quantity * $price;
        $charge = $type === Status::BUY_HISTORY ? $amount * 0.01 : 0;
        $vat = $type === Status::BUY_HISTORY ? $amount * 0.15 : 0;

        return [
            'quantity' => $quantity,
            'amount' => $amount,
            'charge' => $charge,
            'vat' => $vat,
            'trx' => getTrx(),
            'type' => $type,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Status::BUY_HISTORY,
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Status::SELL_HISTORY,
        ]);
    }
}

