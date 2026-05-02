<?php

namespace Tests\Feature\JUnit;

use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointStabilityTest extends TestCase
{
    /**
     * Ensures required tables and demo users exist before executing route probes.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            $this->artisan('migrate:fresh', ['--seed' => true]);
        }

        if (!User::where('email', 'admin@fitapp.com')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }
    }

    /**
     * Probes every declared api/v1 endpoint and verifies no route returns 5xx.
     *
     * SRP: Focuses only on endpoint runtime stability, not business assertions.
     * KISS: Uses a single deterministic sweep with permissive success criteria.
     *
     * @return void
     */
    public function test_api_v1_endpoints_do_not_raise_server_errors(): void
    {
        $admin = User::where('email', 'admin@fitapp.com')->firstOrFail();

        $acceptedStatuses = [
            200, 201, 202, 204,
            302,
            400, 401, 403, 404, 405, 409, 410, 412, 415, 422, 429,
        ];

        $failures = [];

        /** @var array<int, Route> $routes */
        $routes = app('router')->getRoutes()->getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();

            if (!str_starts_with($uri, 'api/v1')) {
                continue;
            }

            $methods = array_values(array_filter(
                $route->methods(),
                static fn (string $method): bool => !in_array($method, ['HEAD', 'OPTIONS'], true)
            ));

            if ($methods === []) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            $requiresAuth = $this->requiresSanctum($middleware);

            $probeUri = $this->buildProbeUri($uri);

            foreach ($methods as $method) {
                if ($requiresAuth) {
                    Sanctum::actingAs($admin);
                }

                $payload = $this->buildPayload($method, $uri);

                try {
                    $response = $this->json($method, '/' . $probeUri, $payload, [
                        'Accept' => 'application/json',
                    ]);

                    $status = $response->status();

                    if (!in_array($status, $acceptedStatuses, true)) {
                        $failures[] = sprintf(
                            '%s /%s returned unexpected status %d',
                            $method,
                            $probeUri,
                            $status
                        );
                    }
                } catch (\Throwable $exception) {
                    $failures[] = sprintf(
                        '%s /%s threw exception: %s',
                        $method,
                        $probeUri,
                        $exception->getMessage()
                    );
                }
            }
        }

        $this->assertSame([], $failures, implode(PHP_EOL, $failures));
    }

    /**
     * Determines whether route middleware requires Sanctum authentication.
     *
     * @param  array<int, string>  $middleware
     * @return bool
     */
    private function requiresSanctum(array $middleware): bool
    {
        foreach ($middleware as $item) {
            if (str_contains($item, 'sanctum')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replaces route placeholders with deterministic probe values.
     *
     * @param  string  $uri
     * @return string
     */
    private function buildProbeUri(string $uri): string
    {
        $replacements = [
            '{id}' => '999999',
            '{hash}' => 'invalid-hash',
            '{routineId}' => '999999',
            '{exerciseId}' => '999999',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $uri);
    }

    /**
     * Builds minimal payloads that trigger validation safely for write methods.
     *
     * @param  string  $method
     * @param  string  $uri
     * @return array<string, mixed>
     */
    private function buildPayload(string $method, string $uri): array
    {
        if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return [];
        }

        if ($uri === 'api/v1/auth/login') {
            return [
                'email' => 'admin@fitapp.com',
                'password' => 'password',
            ];
        }

        if ($uri === 'api/v1/auth/register') {
            return [
                'username' => 'probeuser',
                'email' => 'probe@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'full_name' => 'Probe User',
                'dni' => '11111111H',
                'birth_date' => '1990-01-01',
            ];
        }

        return [
            '_stability_probe' => true,
        ];
    }
}
