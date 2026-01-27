<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\Payment;

class InvoiceAdjustmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_credit_adjustment_and_remaining_updates()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 200]);

        // create invoice
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-1',
            'patient_id' => $patient->id,
            'subtotal' => 200,
            'discount' => 0,
            'tax' => 0,
            'total' => 200,
            'status' => 'partial'
        ]);

        // add a payment of 50
        Payment::create(['invoice_id' => $invoice->id, 'amount' => 50, 'patient_id' => $patient->id, 'method' => 'cash']);

        // initial remaining should consider payments
        $this->assertEquals(150, $invoice->payments()->sum('amount') ? $invoice->total - $invoice->payments()->sum('amount') : $invoice->total);

        // create a credit adjustment of 20 (reduces remaining)
        $payload = ['type' => 'credit', 'amount' => 20, 'reason' => 'discount note'];
        $this->actingAs($admin)->postJson('/api/invoices/'.$invoice->id.'/adjustments', $payload)->assertStatus(201)->assertJsonFragment(['amount' => 20]);

        $invoice->refresh();
        // effective total = 200 - 20 = 180; paid = 50 => remaining = 130
        $this->assertDatabaseHas('adjustments', ['invoice_id' => $invoice->id, 'amount' => 20, 'type' => 'credit']);
        $this->assertEquals(130, (float) $invoice->remaining);
    }

    public function test_admin_can_add_debit_adjustment_and_remaining_increases()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 100]);

        $invoice = Invoice::create(['invoice_number' => 'INV-TEST-2','patient_id'=>$patient->id,'subtotal'=>100,'discount'=>0,'tax'=>0,'total'=>100,'status'=>'unpaid']);

        // add debit adjustment (increase total) of 30
        $payload = ['type' => 'debit', 'amount' => 30, 'reason' => 'additional service'];
        $this->actingAs($admin)->postJson('/api/invoices/'.$invoice->id.'/adjustments', $payload)->assertStatus(201)->assertJsonFragment(['amount' => 30]);

        $invoice->refresh();
        // effective total = 100 + 30 = 130; no payments => remaining = 130
        $this->assertEquals(130, (float) $invoice->remaining);
    }
}
