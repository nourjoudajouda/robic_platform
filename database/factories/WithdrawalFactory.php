<?php

namespace Database\Factories;

use App\Models\Withdrawal;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalFactory extends Factory
{
    protected $model = Withdrawal::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 500, 20000);
        $charge = $amount * 0.02; // 2% charge
        $afterCharge = $amount - $charge;
        $rate = 1;
        $finalAmount = $afterCharge * $rate;
        
        return [
            'amount' => $amount,
            'currency' => 'SAR',
            'rate' => $rate,
            'charge' => $charge,
            'after_charge' => $afterCharge,
            'final_amount' => $finalAmount,
            'trx' => getTrx(),
            'status' => $this->faker->randomElement([
                Status::PAYMENT_SUCCESS,
                Status::PAYMENT_PENDING,
                Status::PAYMENT_REJECT
            ]),
            'withdraw_information' => (object)[
                'account_number' => $this->faker->bankAccountNumber(),
                'bank_name' => $this->faker->randomElement(['Al Rajhi Bank', 'Alinma Bank', 'SABB', 'NCB']),
            ],
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

