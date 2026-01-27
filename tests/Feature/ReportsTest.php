<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Payment;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_endpoint_returns_sums()
    {
        $invoice = Invoice::factory()->create(['total' => 500]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 200]);

        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->getJson('/api/reports/income');
        $response->assertStatus(200)->assertJsonStructure(['total_invoiced','total_paid']);
    }
}
