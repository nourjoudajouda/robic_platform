<?php

namespace Database\Factories;

use App\Models\Product;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $products = [
            ['name_en' => 'Arabica Coffee', 'name_ar' => 'قهوة أرابيكا', 'sku' => 'ARB-001'],
            ['name_en' => 'Robusta Coffee', 'name_ar' => 'قهوة روبوستا', 'sku' => 'ROB-001'],
            ['name_en' => 'Ethiopian Coffee', 'name_ar' => 'قهوة إثيوبية', 'sku' => 'ETH-001'],
            ['name_en' => 'Colombian Coffee', 'name_ar' => 'قهوة كولومبية', 'sku' => 'COL-001'],
            ['name_en' => 'Brazilian Coffee', 'name_ar' => 'قهوة برازيلية', 'sku' => 'BRA-001'],
        ];

        $product = $this->faker->randomElement($products);

        return [
            'name_en' => $product['name_en'],
            'name_ar' => $product['name_ar'],
            'name' => $product['name_en'],
            'sku' => $product['sku'] . '-' . $this->faker->numerify('###'),
            'status' => Status::ENABLE,
            'market_price' => $this->faker->randomFloat(2, 50, 200),
        ];
    }
}

