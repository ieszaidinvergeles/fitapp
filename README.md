# Voltgym

## Project Structure

```text
Voltgym/
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
   git clone https://github.com/ieszaidinvergeles/Voltgym.git
   cd Voltgym/voltgym-infra
   ```

2. **Create the environment file if needed:**

   ```bash
   cp .env.example .env
   ```

3. **Execute Deployment Script:**

   ```bash
   chmod +x ./scripts/voltgym-deploy.sh ./docker/laravel/entrypoint.sh
   ./scripts/voltgym-deploy.sh
   ```

*Containers are configured with `restart: unless-stopped`, so they will restart automatically after a host reboot unless explicitly stopped.*

### Option B: Hosting on Windows (Local Development)

1. **Clone the repository and enter the infra folder:**

   ```powershell
   git clone https://github.com/ieszaidinvergeles/Voltgym.git
   cd Voltgym/voltgym-infra
   ```

2. **Create the environment file:**

   ```powershell
   copy .env.example .env
   ```

3. **Launch the application:**

   ```powershell
   docker compose up -d --build
   ```

### Updating an existing installation

Use these commands when the app is already installed and you want to apply new repository changes.

#### On Linux

1. Pull the latest repository changes:

   ```bash
   cd Voltgym/voltgym-infra
   git pull origin main
   ```

2. Recreate services without rebuilding unless needed:

   ```bash
   docker compose up -d
   ```

3. If Dockerfile or image changes are required, rebuild first:

   ```bash
   docker compose build --no-cache
   docker compose up -d
   ```

#### On Windows

1. Update the repository:

   ```powershell
   cd Voltgym/voltgym-infra
   git pull origin main
   ```

2. Start services with the latest code:

   ```powershell
   docker compose up -d
   ```

3. If image rebuild is needed:

   ```powershell
   docker compose build --no-cache
   docker compose up -d
   ```

### Stopping, starting, and restarting the deployed host

These commands work from the `Voltgym/voltgym-infra` folder after installation.

#### Linux host

- **Stop all services:**

  ```bash
  docker compose down
  ```

- **Start services again:**

  ```bash
  docker compose up -d
  ```

- **Restart running services:**

  ```bash
  docker compose restart
  ```

Because the containers use `restart: unless-stopped`, they will return automatically after a system reboot unless you stop them with `docker compose down`.

#### Windows host

- **Stop all services:**

  ```powershell
  docker compose down
  ```

- **Start services again:**

  ```powershell
  docker compose up -d
  ```

- **Restart running services:**

  ```powershell
  docker compose restart
  ```

If Docker Desktop is stopped, start Docker Desktop first, then run `docker compose up -d` in the `voltgym-infra` folder.

### Notes for managed deployments

- If the host was shut down completely, start the VM/container host first.
- After boot, run `docker compose up -d` from `Voltgym/voltgym-infra` to ensure all services come up.
- Use `docker compose ps` to verify service status.

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
echo "0 */2 * * * cd /home/voltgym/Voltgym/voltgym-infra && ./scripts/auto-update.sh >> /var/log/voltgym-auto-update.log 2>&1" | crontab -
```

This tells the Ubuntu host to execute the script every 2 hours continuously while the server is alive.

## Troubleshooting

### WordPress Custom Theme or Plugin Issues

If activating a custom theme or plugin breaks WordPress (e.g., white screen or errors), deactivate it via the database.

#### Deactivate Broken Custom Theme

1. Access the MySQL container:

   ```bash
   docker exec -it voltgym_mysql mysql -u voltgym_user -p voltgym_wordpress
   ```

   (Enter the MySQL password when prompted.)

2. Switch to a default theme (e.g., Twenty Twenty-Four):

   ```sql
   UPDATE wp_options SET option_value = 'twentytwentyfour' WHERE option_name = 'template';
   UPDATE wp_options SET option_value = 'twentytwentyfour' WHERE option_name = 'stylesheet';
   ```

3. Exit MySQL and restart WordPress:

   ```bash
   docker compose restart wordpress
   ```

#### Deactivate Broken Custom Plugin

To deactivate all plugins (if a custom plugin causes issues):

1. Access the MySQL container:

   ```bash
   docker exec -it voltgym_mysql mysql -u voltgym_user -p voltgym_wordpress
   ```

2. Deactivate all plugins:

   ```sql
   UPDATE wp_options SET option_value = 'a:0:{}' WHERE option_name = 'active_plugins';
   ```

3. Exit MySQL and restart WordPress:

   ```bash
   docker compose restart wordpress
   ```

For specific plugin deactivation, inspect the `active_plugins` value (serialized array) and remove the problematic plugin entry.
