<?php

namespace Database\Factories;

use App\Models\MerchSale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MerchSale>
 */
class MerchSaleFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = MerchSale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(1, 10),
            'item_name' => fake()->word(),
            'price' => fake()->randomFloat(2, 1, 100),
            'read' => 0,
            'user_id' => 1,
            'follower_id' => fake()->numberBetween(1, 300),
            'created_at' => fake()->dateTimeBetween('-3 month', 'now')
        ];
    }
}
