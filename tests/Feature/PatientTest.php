<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Patient;
use App\Models\User;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_patient()
    {
        $payload = [
            'first_name' => 'Ahmed',
            'last_name' => 'Saleh',
            'phone' => '+201001234567',
            'email' => 'ahmed@example.test'
        ];

        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->postJson('/api/patients', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['first_name' => 'Ahmed']);

        $this->assertDatabaseHas('patients', ['email' => 'ahmed@example.test']);
    }

    public function test_can_search_patient()
    {
        Patient::factory()->create(['first_name' => 'Sara', 'phone' => '0109999888']);
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/patients?q=Sara');
        $response->assertStatus(200);
        $this->assertStringContainsString('Sara', $response->getContent());
    }
}
