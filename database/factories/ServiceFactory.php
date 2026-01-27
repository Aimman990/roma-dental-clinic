<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition()
    {
        return [
            'code' => 'SVC-' . $this->faker->unique()->numerify('###'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 20, 500),
            'doctor_id' => User::factory(),
        ];
    }
}
