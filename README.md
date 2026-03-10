# Gym

## 1. Project Structure

```
fitapp/
  server/        — Laravel backend (REST API)
    docs/        — Backend documentation
  wordpress/     — WordPress frontend
    docs/        — Frontend documentation
  docs/          — General project documentation
```

## 2. Documentation

Each part of the project maintains its own `docs/` folder covering the decisions, conventions and architecture specific to that layer.

### 2.1. Backend — `server/docs/`

| Document | Description |
|---|---|
| `database.md` | Database schema, migration conventions, FK behaviors, fixes applied |
| `logs.md` | Log system architecture, why logs have no FK constraints, table conventions |

### 2.2. Frontend — `wordpress/docs/`

| Document | Description |
|---|---|
| Coming soon | Plugin architecture, HTTP client, token management, shortcode conventions |

### 2.3. General — `docs/` 

Will cover cross-cutting concerns shared between backend and frontend — API contract, authentication flow, deployment, environment setup, and onboarding guide for new contributors.

## 3. Commit Convention

All commits follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification with project-specific scopes.

The pattern is:
```
type(scope): short description
```

**Types:**

**Types:**

| Type | When to use |
|---|---|
| `feat` | New feature or file added |
| `update` | Modification or fix to existing code |
| `docs` | Documentation only changes |
| `fix` | Bug fix |
| `refactor` | Code restructure without changing behavior |
| `chore` | Maintenance tasks, dependency updates |

**Scopes — backend:**

| Scope | What it covers |
|---|---|
| `database` | SQL schema, raw database changes |
| `database-laravel` | Migrations, models, factories, seeders |
| `docs-logs` | Log system documentation |
| `api` | Controllers, routes, FormRequests |
| `auth` | Authentication, Sanctum, middleware |

**Scopes — frontend:**

| Scope | What it covers |
|---|---|
| `plugin` | WordPress plugin base |
| `shortcodes` | Shortcodes and Gutenberg blocks |
| `templates` | HTML templates and views |
| `docs-wp` | Frontend documentation |


## 4. Versioning

Tags follow [Semantic Versioning](https://semver.org/) `vMAJOR.MINOR.PATCH`:

| Segment | When it increments |
|---|---|
| `MAJOR` | Breaking change — incompatible with previous version |
| `MINOR` | New functionality added in a backward-compatible way |
| `PATCH` | Small fix or correction that does not add functionality |

A single tag can group multiple related commits that together complete a logical milestone.

### 4.1. How to choose the right tag

**Use `PATCH` (e.g. `v0.3.0` → `v0.3.1`) when:**
- Fixing a typo or error in documentation
- Correcting a column length or constraint in a migration without changing the schema structure
- Fixing a bug in an existing feature without adding new functionality
- Updating a config value or environment variable

**Use `MINOR` (e.g. `v0.3.0` → `v0.4.0`) when:**
- A new functional block is completed — a controller, a seeder set, a new module
- New documentation is added covering a completed feature
- A new table or group of migrations is added without breaking existing ones
- A new section of the plugin or frontend is implemented

**Use `MAJOR` (e.g. `v0.3.0` → `v1.0.0`) when:**
- The API contract changes in a way that breaks existing consumers
- A table is renamed or a column is removed from the schema
- Authentication method changes
- The project reaches a fully deployable and stable state for the first time (`v1.0.0`)

> While the project is in active development (versions `v0.x.x`), `MAJOR` stays at `0`.
> `v1.0.0` is reserved for the first production-ready release.

### 4.2 Version history

| Tag | Scope | Commits included |
|---|---|---|
| `v0.1.0` | Project setup | Initial commit, `.env.example` |
| `v0.2.0` | Database | SQL schema draft, Laravel Eloquent implementation, fixes and docs folder |
| `v0.3.0` | Log system | Log system documentation, 6 log migrations approved |