<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 1, 100),
            'currency' => 'CAD',
            'donation_message' => fake()->randomElement(['Thank you for being awesome', 'Like you', 'You are the best']),
            'read' => 0,
            'user_id' => 1,
            'created_at' => fake()->dateTimeBetween('-3 month', 'now')
        ];
    }
}
