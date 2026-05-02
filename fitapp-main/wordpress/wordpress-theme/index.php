<?php
/**
 * index.php — Theme Entry Point
 *
 * WordPress-style front controller that redirects to the appropriate page
 * based on the current authentication state and role.
 *
 * SRP: Solely responsible for the bootstrap redirect decision.
 * DIP: Depends on the functions.php abstraction layer, not on raw $_SESSION.
 */
require_once 'functions.php';

if (is_logged_in()) {
    if (is_advanced()) {
        header('Location: page-staff-dashboard.php');
    } else {
        header('Location: page-client-dashboard.php');
    }
} else {
    header('Location: front-page.php');
}
exit;
