<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $cities = ['Riyadh', 'Jeddah', 'Dammam', 'Mecca', 'Medina'];
        $city = $this->faker->randomElement($cities);
        
        return [
            'name_en' => $this->faker->company() . ' Warehouse',
            'name_ar' => 'مستودع ' . $this->faker->company(),
            'name' => $this->faker->company() . ' Warehouse',
            'location_en' => $city,
            'location_ar' => $city,
            'location' => $city,
            'code' => 'WH-' . $this->faker->unique()->numerify('####'),
            'address_en' => $this->faker->address(),
            'address_ar' => $this->faker->address(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(24, 32),
            'longitude' => $this->faker->longitude(38, 50),
            'manager_name_en' => $this->faker->name(),
            'manager_name_ar' => $this->faker->name(),
            'manager_name' => $this->faker->name(),
            'mobile' => $this->faker->numerify('5#######'),
            'max_capacity_unit' => $this->faker->randomFloat(2, 1000, 10000),
            'max_capacity_kg' => $this->faker->randomFloat(2, 5000, 50000),
            'area_sqm' => $this->faker->randomFloat(2, 500, 5000),
            'status' => Status::ENABLE,
        ];
    }
}

