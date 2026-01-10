<?php

namespace Database\Factories;

use App\Models\BatchSellOrder;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchSellOrderFactory extends Factory
{
    protected $model = BatchSellOrder::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(10, 500); // أرقام صحيحة
        $sellPrice = $this->faker->randomFloat(2, 50, 200);

        return [
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'sell_price' => $sellPrice,
            'sell_order_code' => BatchSellOrder::generateSellOrderCode(),
            'status' => $this->faker->randomElement([
                Status::SELL_ORDER_ACTIVE,
                Status::SELL_ORDER_SOLD,
                Status::SELL_ORDER_INACTIVE,
            ]),
        ];
    }
}

