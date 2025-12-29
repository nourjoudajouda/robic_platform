<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $qualityGrades = ['Premium', 'Grade A', 'Grade B', 'Standard'];
        $originCountries = ['Ethiopia', 'Colombia', 'Brazil', 'Yemen', 'Kenya'];

        return [
            'batch_code' => Batch::generateBatchCode(),
            'units_count' => $this->faker->randomFloat(2, 100, 5000),
            'items_count_per_unit' => $this->faker->randomFloat(2, 1, 10),
            'sell_price' => $this->faker->randomFloat(2, 50, 200),
            'buy_price' => $this->faker->randomFloat(2, 40, 180),
            'quality_grade' => $this->faker->randomElement($qualityGrades),
            'origin_country' => $this->faker->randomElement($originCountries),
            'exp_date' => $this->faker->dateTimeBetween('+1 year', '+3 years'),
            'status' => Status::ENABLE,
            'type' => 'admin_created',
        ];
    }
}

