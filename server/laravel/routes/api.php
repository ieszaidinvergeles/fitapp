<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * GymApp API routes.
 *
 * SRP: Solely responsible for mapping HTTP endpoints to controller actions.
 *
 * Auth route groups:
 *   Public  — accessible without a token
 *   Private — require a valid Sanctum token (auth:api)
 */

Route::prefix('v1')->group(function (): void {

    Route::prefix('auth')->group(function (): void {

        // Public routes — no token required
        Route::post('/register',       [AuthController::class, 'register']);
        Route::post('/login',          [AuthController::class, 'login']);
        Route::post('/forgot-password',[AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        // Private routes — valid Sanctum token required
        Route::middleware('auth:api')->group(function (): void {
            Route::post('/logout',                    [AuthController::class, 'logout']);
            Route::get('/me',                         [AuthController::class, 'me']);
            Route::post('/email/resend',              [AuthController::class, 'resendVerification']);
            Route::get('/email/verify/{id}/{hash}',   [AuthController::class, 'verifyEmail'])
                ->name('verification.verify')
                ->middleware('signed');
        });

    });

});
