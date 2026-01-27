<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition()
    {
        return [
            'patient_id' => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'doctor_id' => User::factory(),
            'diagnosis' => $this->faker->optional()->sentence(),
            'treatment' => $this->faker->optional()->sentence(),
            'teeth' => null,
            'prescription' => $this->faker->optional()->sentence(),
            'xray_path' => null,
        ];
    }
}
