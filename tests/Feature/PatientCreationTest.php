<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_patient()
    {
        $staff = User::factory()->create(['role' => 'user']);

        $payload = [
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'phone' => '0123456789'
        ];

        $this->actingAs($staff)->postJson('/api/patients', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['first_name' => 'Test']);

        $this->assertDatabaseHas('patients', ['first_name' => 'Test']);
    }

    public function test_guest_cannot_create_patient()
    {
        $payload = [
            'first_name' => 'Guest',
            'last_name' => 'Try'
        ];

        $this->postJson('/api/patients', $payload)
            ->assertStatus(401);
    }
}
