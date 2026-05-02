<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_api_catalog_endpoint_is_available(): void
    {
        $response = $this->get('/api/v1/activities');

        $response->assertStatus(200);
    }
}
