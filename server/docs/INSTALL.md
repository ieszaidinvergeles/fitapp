# Auth System — Installation Guide

## 1. Install Sanctum

```bash
composer require laravel/sanctum
```

## 2. Install the API routes file (Laravel 11)

```bash
php artisan install:api
```

This creates `routes/api.php` and registers it automatically. Replace the
generated file with the one provided in this package.

## 3. Publish Sanctum configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## 4. Run the new migration

```bash
php artisan migrate
```

This adds `email_verified_at` to the `users` table. This column is required
by Laravel's `MustVerifyEmail` contract and was absent from the original schema.

## 5. Register middleware aliases

In `bootstrap/app.php`, locate the `->withMiddleware()` call and add:

```php
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdvancedMiddleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin'    => AdminMiddleware::class,
        'advanced' => AdvancedMiddleware::class,
    ]);
})
```

## 6. Configure .env for email

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="GymApp"
```

For local development you can use Mailtrap or set `MAIL_MAILER=log` to write
emails to the Laravel log instead of sending them.

## 7. Configure token expiration (optional)

In `config/sanctum.php`:

```php
'expiration' => 60 * 24 * 7, // 7 days in minutes — adjust as needed
```

## Files provided in this package

```
app/
  Models/User.php                              — Updated model
  Http/
    Controllers/Auth/AuthController.php        — All auth endpoints
    Middleware/AdminMiddleware.php
    Middleware/AdvancedMiddleware.php
    Requests/Auth/LoginRequest.php
    Requests/Auth/RegisterRequest.php
    Requests/Auth/ForgotPasswordRequest.php
    Requests/Auth/ResetPasswordRequest.php
    Requests/Auth/ResendVerificationRequest.php
  Notifications/VerifyEmailNotification.php
config/auth.php
routes/api.php
database/migrations/add_email_verified_at_to_users_table.php
```
