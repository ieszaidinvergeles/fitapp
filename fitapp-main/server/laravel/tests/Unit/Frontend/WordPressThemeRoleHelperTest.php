<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class WordPressThemeRoleHelperTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $root = dirname(__DIR__, 5);
        require_once $root . DIRECTORY_SEPARATOR . 'wordpress/wordpress-theme/functions.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_assistant_is_treated_as_staff_portal_user_with_member_management_access(): void
    {
        $_SESSION['user'] = ['role' => 'assistant'];

        $this->assertTrue(is_advanced());
        $this->assertTrue(can_manage_members());
        $this->assertTrue(is_assistant());
        $this->assertSame('page-staff-dashboard.php', get_role_home_path());
    }

    public function test_client_keeps_client_dashboard_home_path(): void
    {
        $_SESSION['user'] = ['role' => 'client'];

        $this->assertFalse(is_advanced());
        $this->assertFalse(can_manage_members());
        $this->assertSame('page-client-dashboard.php', get_role_home_path());
    }

    public function test_api_message_normalizes_general_and_validation_errors(): void
    {
        $this->assertSame('OK', api_message(['message' => ['general' => 'OK']]));

        $message = api_message([
            'message' => [
                'email' => ['Invalid email'],
                'password' => ['Password too short'],
            ],
        ]);

        $this->assertStringContainsString('email: Invalid email', $message);
        $this->assertStringContainsString('password: Password too short', $message);
    }
}
