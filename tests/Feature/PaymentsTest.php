<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payment()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $invoice = Invoice::factory()->create(['total' => 100, 'status' => 'paid']);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100,
            'patient_id' => $invoice->patient_id,
            'received_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $payment->id]);
    }

    public function test_admin_can_delete_payment_and_update_invoice_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $invoice = Invoice::factory()->create(['total' => 100, 'status' => 'paid']);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100,
            'patient_id' => $invoice->patient_id,
            'received_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);

        $this->actingAs($admin)
            ->deleteJson("/api/payments/{$payment->id}")
            ->assertOk()
            ->assertJson(['message' => 'deleted']);

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'unpaid']);
    }

    public function test_non_admin_cannot_show_or_delete_payment()
    {
        $user = User::factory()->create(['role' => 'user']);

        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 10,
            'patient_id' => $invoice->patient_id,
        ]);

        $this->actingAs($user)->getJson("/api/payments/{$payment->id}")->assertStatus(403);
        $this->actingAs($user)->deleteJson("/api/payments/{$payment->id}")->assertStatus(403);
    }

    public function test_admin_can_create_payment_and_invoice_remaining_updates()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $invoice = Invoice::factory()->create(['total' => 200, 'status' => 'unpaid']);

        $payload = [ 'invoice_id' => $invoice->id, 'patient_id' => $invoice->patient_id, 'amount' => 50, 'method' => 'cash' ];

        $this->actingAs($admin)->postJson('/api/payments', $payload)->assertStatus(201)->assertJsonFragment(['amount' => 50]);

        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 50]);

        $invoice->refresh();
        $this->assertEquals(150, (float) $invoice->remaining);
        $this->assertEquals('partial', $invoice->status);
    }
}
