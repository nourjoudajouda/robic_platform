<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['+', '-'];
        $type = $this->faker->randomElement($types);
        $amount = $this->faker->randomFloat(2, 100, 10000);
        
        return [
            'amount' => $amount,
            'post_balance' => $this->faker->randomFloat(2, 0, 100000),
            'charge' => $this->faker->randomFloat(2, 0, 100),
            'trx_type' => $type,
            'details' => $this->faker->sentence(),
            'trx' => getTrx(),
            'remark' => $this->faker->randomElement(['deposit', 'withdrawal', 'buy_bean', 'sell_bean', 'transfer']),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

