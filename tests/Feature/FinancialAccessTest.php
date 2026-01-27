<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class FinancialAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_reports()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->getJson('/api/reports/income')->assertStatus(403);
    }

    public function test_non_admin_cannot_manage_invoices_or_payments()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->postJson('/api/invoices', [])->assertStatus(403);
        $this->actingAs($user)->getJson('/api/invoices')->assertStatus(403);

        $this->actingAs($user)->postJson('/api/payments', [])->assertStatus(403);
        $this->actingAs($user)->getJson('/api/payments')->assertStatus(403);
    }

    public function test_non_admin_cannot_access_expenses_or_salaries()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->getJson('/api/expenses')->assertStatus(403);
        $this->actingAs($user)->postJson('/api/salaries/generate')->assertStatus(403);
    }
}
