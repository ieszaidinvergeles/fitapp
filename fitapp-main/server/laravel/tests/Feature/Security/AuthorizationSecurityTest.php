<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            $this->artisan('migrate:fresh', ['--seed' => true]);
        }
    }

    public function test_user_management_security_boundaries_hold(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();

        Sanctum::actingAs(User::where('email', 'staff1@fitapp.com')->firstOrFail());

        $this->getJson('/api/v1/users')->assertForbidden();

        $assistant = User::where('email', 'assistant@fitapp.com')->firstOrFail();
        $client = User::where('email', 'client1@fitapp.com')->firstOrFail();

        Sanctum::actingAs($assistant);

        $this->putJson('/api/v1/users/' . $client->id, [
            'role' => 'manager',
        ])->assertForbidden()
          ->assertJsonPath('message.general', 'Assistant users may only manage client-facing accounts.');

        $manager = User::where('email', 'manager@fitapp.com')->firstOrFail();

        Sanctum::actingAs($manager);

        $this->putJson('/api/v1/users/' . $client->id, [
            'role' => 'admin',
        ])->assertForbidden()
          ->assertJsonPath('message.general', 'Managers may not create or modify admin accounts.');
    }
}
