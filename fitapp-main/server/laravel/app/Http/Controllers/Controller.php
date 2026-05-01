<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller class for all application controllers.
 *
 * SRP: Provides a shared base for dependency injection and shared behaviour.
 * OCP: Extended by all feature controllers without modifying this class.
 * LSP: All child controllers can substitute this base safely.
 * DIP: Uses the AuthorizesRequests trait to delegate authorization to the Gate abstraction.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
}

