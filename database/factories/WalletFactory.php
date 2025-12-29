<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'balance' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => Status::ENABLE,
        ];
    }
}

