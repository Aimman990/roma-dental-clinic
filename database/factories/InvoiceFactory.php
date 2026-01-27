<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 50, 1000);
        $discount = 0;
        $tax = 0;
        return [
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => max(0,$subtotal - $discount + $tax),
            'status' => 'unpaid'
        ];
    }
}
