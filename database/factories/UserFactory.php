<?php

namespace Database\Factories;

use App\Models\User;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $types = ['user', 'establishment'];
        $type = $this->faker->randomElement($types);
        
        $data = [
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'dial_code' => '+966',
            'mobile' => $this->faker->numerify('5#######'),
            'country_name' => 'Saudi Arabia',
            'country_code' => 'SA',
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'status' => Status::USER_ACTIVE,
            'kv' => Status::KYC_VERIFIED,
            'ev' => Status::VERIFIED,
            'sv' => Status::VERIFIED,
            'profile_complete' => Status::YES,
            'type' => $type,
            'user_type' => $type,
            'balance' => $this->faker->randomFloat(2, 1000, 100000),
        ];

        if ($type === 'establishment') {
            $data['establishment_name'] = $this->faker->company();
            $data['commercial_registration'] = $this->faker->numerify('##########');
            $data['establishment_info'] = $this->faker->paragraph();
        }

        return $data;
    }

    public function establishment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'establishment',
            'user_type' => 'establishment',
            'establishment_name' => $this->faker->company(),
            'commercial_registration' => $this->faker->numerify('##########'),
            'establishment_info' => $this->faker->paragraph(),
        ]);
    }
}

