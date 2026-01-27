<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRegistrationAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_blocked_when_admin_exists()
    {
        // seed an admin user
        User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'New User',
            'email' => 'new@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ];

        // posting to web register should be forbidden (403)
        $this->post('/register', $payload)->assertStatus(403);
    }

    public function test_admin_can_create_user_via_api()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'Staff Member',
            'email' => 'staff2@example.test',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
            'role' => 'user'
        ];

        $this->actingAs($admin)->postJson('/api/users', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['email' => 'staff2@example.test']);

        $this->assertDatabaseHas('users', ['email' => 'staff2@example.test']);
    }
}
