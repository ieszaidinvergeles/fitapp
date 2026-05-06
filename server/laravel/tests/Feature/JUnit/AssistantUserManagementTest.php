<?php

namespace Tests\Feature\JUnit;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssistantUserManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            $this->artisan('migrate:fresh', ['--seed' => true]);
        }
    }

    public function test_assistant_member_management_flow_is_valid(): void
    {
        $assistant = User::where('email', 'assistant@fitapp.com')->firstOrFail();

        Sanctum::actingAs($assistant);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonPath('message.general', 'OK');

        $this->postJson('/api/v1/users', [
            'username' => 'assistantclient',
            'email' => 'assistant-client@fitapp.com',
            'password_hash' => 'password123',
            'role' => 'client',
            'full_name' => 'Assistant Created Client',
            'dni' => '12345678Z',
            'birth_date' => '1997-05-10',
            'current_gym_id' => $assistant->current_gym_id,
            'membership_status' => 'expired',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'assistant-client@fitapp.com',
            'role' => 'client',
        ]);

        $this->postJson('/api/v1/users', [
            'username' => 'blockedstaff',
            'email' => 'blocked-staff@fitapp.com',
            'password_hash' => 'password123',
            'role' => 'staff',
            'full_name' => 'Blocked Staff',
            'dni' => '87654321X',
            'birth_date' => '1995-03-15',
            'current_gym_id' => $assistant->current_gym_id,
            'membership_status' => 'expired',
        ])->assertForbidden()
          ->assertJsonPath('message.general', 'Assistant users may only manage client-facing accounts.');
    }
}
