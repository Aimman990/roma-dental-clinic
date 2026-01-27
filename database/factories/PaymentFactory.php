<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $amount = $this->faker->randomFloat(2, 10, 500);
        return [
            'invoice_id' => Invoice::factory(),
            'patient_id' => Patient::factory(),
            'amount' => $amount,
            'method' => $this->faker->randomElement(['cash','card','bank_transfer']),
            'reference' => $this->faker->optional()->bothify('REF-####'),
            'received_by' => User::factory(),
            'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . strtoupper($this->faker->bothify('??##')),
        ];
    }
}
