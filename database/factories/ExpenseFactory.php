<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'category' => $this->faker->randomElement(['salary','materials','rent','utilities','gov_fees','other']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'incurred_on' => $this->faker->dateTimeBetween('-6 months','now')->format('Y-m-d'),
            'receipt_path' => null,
            'recorded_by' => User::factory(),
        ];
    }
}
