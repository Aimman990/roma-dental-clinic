<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $service = Service::factory();
        $quantity = $this->faker->numberBetween(1,3);
        $unit = $this->faker->randomFloat(2, 20, 200);
        return [
            'invoice_id' => Invoice::factory(),
            'service_id' => $service,
            'description' => $this->faker->sentence(4),
            'quantity' => $quantity,
            'unit_price' => $unit,
            'subtotal' => $quantity * $unit,
        ];
    }
}
