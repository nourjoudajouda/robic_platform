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
            ['name_en' => 'Arabica Coffee', 'name_ar' => 'قهوة أرابيكا'],
            ['name_en' => 'Robusta Coffee', 'name_ar' => 'قهوة روبوستا'],
            ['name_en' => 'Ethiopian Coffee', 'name_ar' => 'قهوة إثيوبية'],
            ['name_en' => 'Colombian Coffee', 'name_ar' => 'قهوة كولومبية'],
            ['name_en' => 'Brazilian Coffee', 'name_ar' => 'قهوة برازيلية'],
        ];

        $product = $this->faker->randomElement($products);
        
        // Generate SKU in format RO-XXX (like in ProductController)
        $prefix = 'RO';
        $sku = '';
        do {
            $number = getNumber(3);
            $sku = $prefix . '-' . $number;
        } while (\App\Models\Product::where('sku', $sku)->exists());

        return [
            'name_en' => $product['name_en'],
            'name_ar' => $product['name_ar'],
            'name' => $product['name_en'],
            'sku' => $sku,
            'status' => Status::ENABLE,
            'market_price' => $this->faker->randomFloat(2, 50, 200),
        ];
    }
}

