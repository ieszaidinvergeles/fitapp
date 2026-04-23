# FitApp

## Project Structure

```text
fitapp/
  server/        - Laravel backend (REST API, logic, Migrations)
  wordpress/     - WordPress frontend (Custom Theme & Plugins)
  voltgym-infra/ - Docker Orchestration and Deployment scripts
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
- `docs/infrastructure_setup.md`

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
| `infra` | Docker compose, CI/CD, Nginx configuration |

## Versioning

Tags follow [Semantic Versioning](https://semver.org/) as `vMAJOR.MINOR.PATCH`.

## Installation Guide

The infrastructure follows SOLID and KISS principles, orchestrated using Relative Bind Mounts. 

### Option A: Hosting on Linux (Production Server)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ieszaidinvergeles/fitapp.git
   cd fitapp/voltgym-infra
   ```
2. **Execute Deployment Script:**
   ```bash
   chmod +x ./scripts/voltgym-deploy.sh ./docker/laravel/entrypoint.sh 
   ./scripts/voltgym-deploy.sh
   ```

*Containers are configured with "restart: unless-stopped" and will persist across host reboots.*

### Option B: Hosting on Windows (Local Development)

1. **Clone the repository and enter the infra folder:**
   ```powershell
   git clone https://github.com/ieszaidinvergeles/fitapp.git
   cd fitapp/voltgym-infra
   ```
2. **Create the Environment File:**
   ```powershell
   copy .env.example .env
   ```
3. **Execute Docker Compose Build:**
   ```powershell
   docker compose up -d --build
   ```

## Cloud Firewall Settings (Azure / AWS)

If deploying to a Cloud VM, you must ensure the following inbound ports are open in your Network Security Group (NSG) to make the application accessible from the outside.

| Port | Protocol | Purpose / Location |
|------|----------|-------------------|
| **22** | TCP | SSH Server Access |
| **80** | TCP | WordPress Site (Main User Interface) |
| **8000** | TCP | Laravel API (Direct Backend Access) |
| **8080** | TCP | Adminer (Database Graphical Interface) |
| **3306** | TCP | MySQL Direct Connection (For external database tools) |

## Continuous Deployment Automation

The file `voltgym-infra/scripts/auto-update.sh` periodically checks if the origin repository has new commits. If it discovers changes, it:
1. Pulls the latest code naturally.
2. Updates Composer dependencies silently.
3. Automatically runs `php artisan migrate --force` to inject new database schema rows without destroying WordPress or Database data.

### How to activate it

Run this command from your Linux server shell to install it in the standard cron:

```bash
echo "0 */2 * * * cd /home/voltgym/fitapp/voltgym-infra && ./scripts/auto-update.sh >> /var/log/voltgym-auto-update.log 2>&1" | crontab -
```
This tells the Ubuntu host to execute the script every 2 hours continuously while the server is alive.
