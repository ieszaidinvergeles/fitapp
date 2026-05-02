# Stress Tests on MySQL

## Scope

This suite is a stress-smoke layer rather than a full concurrent benchmark.

Covered flow:
- Repeated reads against public catalogue endpoints.
- Repeated reads against the authenticated staff dashboard.
- No `500` responses under burst-style repeated requests.
- Wall-clock guardrails for the local MySQL + Laravel stack.

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

php vendor/bin/phpunit --testsuite Stress
```

## Result

- Status: `OK`
- Latest run: `1 test, 33 assertions`

## Notes

- This suite is sequential on purpose. It is meant to catch endpoint instability and obvious local performance regressions.
