<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AuditLog;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_delete_user_and_audit_is_recorded()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // create
        $payload = ['name' => 'New User', 'email' => 'staff.new@example.test', 'password' => 'pass1234', 'password_confirmation' => 'pass1234', 'role' => 'user'];
        $this->actingAs($admin)->postJson('/api/users', $payload)->assertStatus(201)->assertJsonFragment(['email' => 'staff.new@example.test']);

        $this->assertDatabaseHas('users', ['email' => 'staff.new@example.test']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'Created user: staff.new@example.test']);

        $user = User::where('email','staff.new@example.test')->firstOrFail();

        // update
        $this->actingAs($admin)->putJson('/api/users/'.$user->id, ['name'=>'Updated Name'])->assertStatus(200)->assertJsonFragment(['name' => 'Updated Name']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'Updated user: ' . $user->email]);

        // delete
        $this->actingAs($admin)->deleteJson('/api/users/'.$user->id)->assertStatus(200);
        $this->assertDatabaseMissing('users', ['email' => 'staff.new@example.test']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'Deleted user: staff.new@example.test']);
    }

    public function test_non_admin_web_cannot_view_users_page()
    {
        $staff = User::factory()->create(['role' => 'user']);
        $this->actingAs($staff)->get('/users')->assertStatus(403);
    }

    public function test_admin_can_create_doctor_without_password()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = ['name' => 'Dr NoPass', 'email' => 'dr.nopass@example.test', 'role' => 'doctor'];
        $this->actingAs($admin)->postJson('/api/users', $payload)->assertStatus(201)->assertJsonFragment(['email' => 'dr.nopass@example.test']);

        $this->assertDatabaseHas('users', ['email' => 'dr.nopass@example.test', 'role' => 'doctor']);
    }
}
