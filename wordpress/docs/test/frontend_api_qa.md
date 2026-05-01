# Frontend API QA

## Scope

This QA layer validates the WordPress client integration points already implemented in `wordpress/wordpress-theme`.

Covered checks:
- Key pages still reference the expected API endpoints.
- `functions.php` keeps the centralized API wrapper and bearer-token flow.
- Role helpers treat `assistant` as staff-portal user and member manager.
- Client roles still resolve to the client dashboard.
- `api_message()` normalizes backend error payloads for the frontend.

## Command

```powershell
php vendor/bin/phpunit --testsuite FrontendQA
```

Run it from:

```text
server/laravel
```

## Result

- Status: `OK`
- Latest run: `5 tests, 35 assertions`
