<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+30 minutes');
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory(),
            'start_at' => $start,
            'end_at' => $end,
            'status' => $this->faker->randomElement(['scheduled','confirmed','completed']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
