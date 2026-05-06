# Frontend Integration and Testing

This document groups frontend-facing integration and testing references for the WordPress theme.

## Related Files

- `frontend_api_qa.md`: automated QA coverage for API contracts used by the theme
- `../wordpress_frontend_integration.md`: page-to-endpoint integration guide for the WordPress team

## Automated Coverage

The current automated frontend checks live in the Laravel PHPUnit suite:

```text
server/laravel/tests/Unit/Frontend
```

Run them from:

```powershell
cd server/laravel
php vendor/bin/phpunit --testsuite FrontendQA
```
