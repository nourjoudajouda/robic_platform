<?php

namespace Database\Factories;

use App\Models\UserSellOrder;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSellOrderFactory extends Factory
{
    protected $model = UserSellOrder::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(4, 10, 500);
        $sellPrice = $this->faker->randomFloat(2, 50, 200);

        return [
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'sell_price' => $sellPrice,
            'buy_price' => $sellPrice * 0.8, // Buy price is 80% of sell price
            'sell_order_code' => UserSellOrder::generateSellOrderCode(),
            'status' => $this->faker->randomElement([
                Status::SELL_ORDER_ACTIVE,
                Status::SELL_ORDER_SOLD,
                Status::SELL_ORDER_INACTIVE,
            ]),
        ];
    }
}

