<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'national_id' => $this->faker->unique()->numerify('###########'),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'birthdate' => $this->faker->date('Y-m-d', '2004-01-01'),
            'gender' => $this->faker->randomElement(['male','female']),
            'notes' => $this->faker->optional()->text(200),
        ];
    }
}
