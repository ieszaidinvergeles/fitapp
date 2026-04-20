# JUnit Tests on MySQL

## Scope

This suite validates backend API regressions that must stay stable after adding the `assistant` role.

Covered flow:
- Public API smoke check for catalogue access.
- Assistant can list users.
- Assistant can create client-facing accounts.
- Assistant cannot create privileged staff accounts.

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

php vendor/bin/phpunit --testsuite JUnit --log-junit build/junit.xml
```

## Result

- Status: `OK`
- Latest run: `2 tests, 7 assertions`
- Artifact: `server/laravel/build/junit.xml`

## Notes

- The reset uses `scripts/reset_testing_database.php` because this schema behaves more reliably with a raw database reset than with repeated `migrate:fresh`.
