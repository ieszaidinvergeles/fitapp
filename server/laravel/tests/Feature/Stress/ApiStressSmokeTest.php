<?php

namespace Tests\Feature\Stress;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiStressSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            $this->artisan('migrate:fresh', ['--seed' => true]);
        }
    }

    public function test_repeated_api_reads_survive_stress_smoke(): void
    {
        $endpoints = ['/api/v1/classes', '/api/v1/activities', '/api/v1/gyms', '/api/v1/recipes'];

        $startedAt = microtime(true);

        foreach (range(1, 5) as $round) {
            foreach ($endpoints as $endpoint) {
                $this->getJson($endpoint)->assertOk();
            }
        }

        Sanctum::actingAs(User::where('email', 'manager@fitapp.com')->firstOrFail());

        foreach (range(1, 12) as $round) {
            $this->getJson('/api/v1/staff/dashboard')->assertOk();
        }

        $this->assertLessThan(20, microtime(true) - $startedAt);
    }
}
