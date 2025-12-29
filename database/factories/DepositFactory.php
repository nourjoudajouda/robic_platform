<?php

namespace Database\Factories;

use App\Models\Deposit;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositFactory extends Factory
{
    protected $model = Deposit::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 500, 50000);
        $charge = $amount * 0.01; // 1% charge
        $finalAmount = $amount + $charge;
        
        return [
            'method_code' => $this->faker->randomElement([1000, 1001, 1002]), // Bank transfer methods
            'amount' => $amount,
            'method_currency' => 'SAR',
            'charge' => $charge,
            'rate' => 1,
            'final_amount' => $finalAmount,
            'trx' => getTrx(),
            'status' => $this->faker->randomElement([
                Status::PAYMENT_SUCCESS,
                Status::PAYMENT_PENDING,
                Status::PAYMENT_REJECT
            ]),
            'description' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

