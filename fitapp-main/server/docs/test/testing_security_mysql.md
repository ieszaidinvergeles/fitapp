# Security Tests on MySQL

## Scope

This suite validates access boundaries and privilege escalation protections.

Covered flow:
- Guest cannot access `/api/v1/users`.
- Staff cannot access user-management endpoints.
- Assistant cannot promote a client to `manager`.
- Manager cannot promote a client to `admin`.

## Command

```powershell
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='3306'
$env:DB_DATABASE='gymapp_testing'
$env:DB_USERNAME='root'
$env:DB_PASSWORD='root'
php scripts/reset_testing_database.php

$env:APP_ENV='testing'
$env:DB_CONNECTION='mysql'
php artisan migrate --seed

php vendor/bin/phpunit --testsuite Security
```

## Result

- Status: `OK`
- Latest run: `1 test, 6 assertions`
