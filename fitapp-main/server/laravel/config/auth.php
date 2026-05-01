<?php

/**
 * Laravel authentication configuration.
 *
 * SRP: Solely responsible for defining guards, providers and password brokers.
 *
 * NOTE: The default web guard is kept for compatibility with Laravel internals
 *       (password reset, email verification). All API routes use the
 *       'api' guard backed by Sanctum.
 */
return [

    'defaults' => [
        'guard'     => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver'   => 'sanctum',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 1,
        ],
    ],

    'password_timeout' => 10800,

    'verification' => [
        'expire' => 60,
    ],

];
