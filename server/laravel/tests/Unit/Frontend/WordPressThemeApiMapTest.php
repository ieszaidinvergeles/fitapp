<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class WordPressThemeApiMapTest extends TestCase
{
    public function test_key_wordpress_pages_keep_the_expected_api_contracts(): void
    {
        $root = dirname(__DIR__, 5);

        $pages = [
            'wordpress/wordpress-theme/front-page.php' => ['/auth/login'],
            'wordpress/wordpress-theme/page-register.php' => ['/auth/register'],
            'wordpress/wordpress-theme/page-forgot-password.php' => ['/auth/forgot-password'],
            'wordpress/wordpress-theme/page-client-dashboard.php' => ['/dashboard'],
            'wordpress/wordpress-theme/page-client-classes.php' => ['/classes', '/bookings'],
            'wordpress/wordpress-theme/page-client-bookings.php' => ['/bookings', '/cancel'],
            'wordpress/wordpress-theme/page-staff-dashboard.php' => ['/staff/dashboard'],
            'wordpress/wordpress-theme/page-staff-attendance.php' => ['/attendance'],
            'wordpress/wordpress-theme/page-staff-admin-users.php' => ['/users'],
        ];

        foreach ($pages as $relativePath => $expectedEndpoints) {
            $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $relativePath);

            $this->assertIsString($contents, $relativePath . ' should be readable.');

            foreach ($expectedEndpoints as $expectedEndpoint) {
                $this->assertStringContainsString($expectedEndpoint, $contents, $relativePath . ' must keep endpoint ' . $expectedEndpoint);
            }
        }
    }

    public function test_theme_functions_keep_the_api_base_path_and_authenticated_wrappers(): void
    {
        $root = dirname(__DIR__, 5);
        $contents = file_get_contents($root . DIRECTORY_SEPARATOR . 'wordpress/wordpress-theme/functions.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString("define('API_BASE',  'http://127.0.0.1:8000/api/v1');", $contents);
        $this->assertStringContainsString("function api_get(string \$endpoint, bool \$auth = false): array", $contents);
        $this->assertStringContainsString("function api_post(string \$endpoint, array \$body = [], bool \$auth = false): array", $contents);
        $this->assertStringContainsString("Authorization: Bearer ", $contents);
    }
}
