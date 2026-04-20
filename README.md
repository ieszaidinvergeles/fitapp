# FitApp

## Project Structure

```text
fitapp/
  server/        - Laravel backend (REST API)
    docs/        - Backend documentation
      test/      - Backend test documentation
  wordpress/     - WordPress frontend
    docs/        - Frontend documentation
      test/      - Frontend test documentation
  docs/          - General project documentation
```

## Documentation Map

Each area keeps its documentation close to the code it describes.

- `server/docs/`: backend architecture, database, API, logic, and security notes
- `server/docs/test/`: backend testing guides and execution notes
- `wordpress/docs/`: frontend implementation and WordPress integration notes
- `wordpress/docs/test/`: frontend QA and integration testing notes
- `docs/`: cross-cutting project documentation shared by backend and frontend

Start with:

- `server/docs/README.md`
- `wordpress/docs/README.md`
- `docs/README.md`

## Commit Convention

All commits follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/):

```text
type(scope): short description
```

| Type | When to use |
|---|---|
| `feat` | New feature or file added |
| `update` | Modification or fix to existing code |
| `docs` | Documentation-only changes |
| `fix` | Bug fix |
| `refactor` | Code restructure without changing behavior |
| `chore` | Maintenance tasks and dependency updates |

### Suggested scopes

| Scope | What it covers |
|---|---|
| `database` | SQL schema and raw database changes |
| `database-laravel` | Migrations, models, factories, seeders |
| `api` | Controllers, routes, FormRequests |
| `auth` | Authentication, Sanctum, middleware |
| `plugin` | WordPress plugin work |
| `templates` | WordPress theme templates and views |
| `docs-backend` | Backend documentation |
| `docs-wp` | Frontend documentation |
| `docs-project` | Root or cross-project documentation |

## Versioning

Tags follow [Semantic Versioning](https://semver.org/) as `vMAJOR.MINOR.PATCH`.
