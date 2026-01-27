<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Invoice;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_invoice_with_items()
    {
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 150]);

        $payload = [
            'patient_id' => $patient->id,
            'items' => [
                ['service_id' => $service->id, 'description'=>'Filling', 'quantity'=>1, 'unit_price'=>150]
            ],
            'discount' => 0,
            'tax' => 0
        ];

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->postJson('/api/invoices', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id]);
    }

    public function test_subtotal_and_total_differ_when_discount_and_tax_present()
    {
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 200]);

        $payload = [
            'patient_id' => $patient->id,
            'items' => [ ['service_id' => $service->id, 'description'=>'Service', 'quantity'=>1, 'unit_price'=>200] ],
            'discount' => 20,
            'tax' => 10
        ];

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->postJson('/api/invoices', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id, 'subtotal' => 200.00, 'discount' => 20.00, 'tax' => 10.00, 'total' => 190.00]);
    }

    public function test_admin_can_create_invoice_with_doctor_and_initial_payment()
    {
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 150]);
        $doctor = User::factory()->create(['role' => 'doctor']);

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'items' => [
                ['service_id' => $service->id, 'description'=>'Filling', 'quantity'=>1, 'unit_price'=>150]
            ],
            'discount' => 0,
            'tax' => 0,
            'initial_payment' => 150
        ];

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->postJson('/api/invoices', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['amount' => 150]);
        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'payment_method' => 'cash', 'remaining' => 0]);
    }

    public function test_admin_can_update_and_delete_invoice()
    {
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 150]);
        $user = User::factory()->create(['role' => 'admin']);

        // create
        $payload = [
            'patient_id' => $patient->id,
            'items' => [ ['description' => 'X', 'quantity' => 1, 'unit_price' => 50] ],
            'discount' => 0,
            'tax' => 0
        ];
        $res = $this->actingAs($user)->postJson('/api/invoices', $payload);
        $res->assertStatus(201);
        $inv = Invoice::first();

        // update with new items and total
        $update = [
            'items' => [ ['description' => 'Changed', 'quantity' => 2, 'unit_price' => 10] ],
            'total' => 20
        ];

        $this->actingAs($user)->putJson('/api/invoices/'.$inv->id, $update)->assertStatus(200)->assertJsonFragment(['total' => 20]);

        // delete
        $this->actingAs($user)->deleteJson('/api/invoices/'.$inv->id)->assertStatus(200);
        $this->assertDatabaseMissing('invoices', ['id' => $inv->id]);
    }
}
