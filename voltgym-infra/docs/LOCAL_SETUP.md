# Volt Gym — Infrastructure Setup Guide

This guide covers how to spin up the full Volt Gym stack locally or on a production Ubuntu server. The entire system runs in Docker, so the process is identical on any host once Docker is installed.

**Stack:**
- Laravel 13 (PHP-FPM) — REST API
- WordPress (PHP-FPM) — Frontend
- MySQL 8.0 — Database
- Adminer — Database UI (port 8080)
- Redis 7 — Cache + Queue
- Nginx — Reverse proxy
- Queue Worker — Laravel async jobs
- Scheduler — Laravel cron tasks

---

## Prerequisites

### Ubuntu (Server or Desktop)

**1. Install Docker Engine**

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

**2. Add your user to the docker group (so you don't need sudo)**

```bash
sudo usermod -aG docker $USER
newgrp docker
```

**3. Verify**

```bash
docker --version
docker compose version
```

---

### Windows

**1. Install Docker Desktop**

Download and install from: https://www.docker.com/products/docker-desktop/

During installation:
- Enable WSL 2 backend (recommended) — Docker Desktop will guide you.
- If WSL 2 is not available, Hyper-V backend also works.

**2. Start Docker Desktop**

Open Docker Desktop from the Start Menu. Wait until the whale icon in the taskbar shows "Docker Desktop is running."

**3. Open a terminal**

Use **PowerShell**, **Windows Terminal**, or **Git Bash**. All commands below work the same way.

**4. Verify**

```powershell
docker --version
docker compose version
```

---

## Setup Steps (same for Ubuntu and Windows)

### Step 1 — Clone the repository

```bash
git clone https://github.com/YOUR_USER/YOUR_REPO.git voltgym
cd voltgym/infra
```

> If the repository is private, you will need to authenticate with GitHub first:
> `git config --global credential.helper store` then enter your credentials on first clone.

---

### Step 2 — Configure environment variables

```bash
cp .env.example .env
```

Open `.env` in any text editor and fill in the required values:

```env
APP_ENV=local
APP_KEY=                        # Leave empty for now — generated in Step 5
APP_URL=http://localhost

DB_ROOT_PASSWORD=your_root_password
DB_DATABASE=voltgym
DB_USERNAME=voltgym_user
DB_PASSWORD=your_db_password

WP_DB_NAME=voltgym_wordpress

REPO_URL=https://github.com/YOUR_USER/YOUR_REPO.git
REPO_BRANCH=main
```

> On Windows, use Notepad, VS Code, or any editor. Do not use Word.

---

### Step 3 — Build and start all containers

```bash
docker compose up -d --build
```

This will:
- Build the Laravel and WordPress PHP-FPM images
- Pull MySQL, Redis, Nginx, and Adminer images
- Create all named volumes
- Start all 8 services

First build takes 3–5 minutes. Subsequent starts take a few seconds.

**Check all services are running:**

```bash
docker compose ps
```

All services should show `running` or `healthy`.

---

### Step 4 — Wait for MySQL to be ready

MySQL takes 20–30 seconds on first start to initialise. You can watch it:

```bash
docker compose logs -f mysql
```

Wait until you see: `ready for connections`

---

### Step 5 — Bootstrap Laravel

```bash
docker compose exec laravel bash -c "
    composer install --no-dev --optimize-autoloader &&
    php artisan key:generate &&
    php artisan migrate &&
    php artisan db:seed &&
    php artisan storage:link &&
    chmod -R 775 storage bootstrap/cache
"
```

This installs PHP dependencies, generates the app key, runs all database migrations, seeds demo data, and sets correct permissions.

---

### Step 6 — Verify the services

| Service | URL | Notes |
|---|---|---|
| Laravel API | http://localhost/api/v1/activities | Should return JSON with activities |
| WordPress | http://localhost:8000 | WordPress frontend |
| Adminer | http://localhost:8080 | DB UI — server: `mysql`, user/pass from `.env` |

---

## Demo Credentials (after seeding)

These accounts are created by the demo seeders and are available after Step 5:

| Role | Email | Password |
|---|---|---|
| Admin | admin@fitapp.com | Admin1234! |
| Manager | manager@fitapp.com | Manager1234! |
| Assistant | assistant@fitapp.com | Assistant1234! |
| Staff | staff@fitapp.com | Staff1234! |
| Client | client@fitapp.com | Client1234! |
| Online user | online@fitapp.com | Online1234! |

---

## Daily Usage

**Start the stack:**
```bash
docker compose up -d
```

**Stop the stack (data is preserved in volumes):**
```bash
docker compose down
```

**Stop and remove all data (full reset):**
```bash
docker compose down -v
```
> ⚠️ The `-v` flag deletes all volumes including the database. Only use this for a clean slate.

**View logs:**
```bash
docker compose logs -f laravel
docker compose logs -f nginx
docker compose logs -f mysql
```

**Run an Artisan command:**
```bash
docker compose exec laravel php artisan migrate:status
docker compose exec laravel php artisan route:list
```

**Access a container shell:**
```bash
docker compose exec laravel bash
docker compose exec mysql bash
```

---

## Backup and Restore Volumes

**Backup all volumes to a local folder:**
```bash
bash scripts/backup-volumes.sh ./my-backup
```

**Restore from a backup:**
```bash
bash scripts/restore-volumes.sh ./my-backup/20260422_120000
```

This is how you move the full environment (including database data and WordPress uploads) between machines.

---

## Production Server Setup (Ubuntu)

For a production Ubuntu server the steps are the same, with the following additions:

**1. Create the install directory and copy the infra folder:**

```bash
sudo mkdir -p /opt/voltgym
sudo chown $USER:$USER /opt/voltgym
cp -r ./infra /opt/voltgym/infra
```

**2. Set `APP_ENV=production` and `APP_URL=http://YOUR_SERVER_IP` in `.env`.**

**3. Run the first-time deploy script:**

```bash
bash /opt/voltgym/infra/scripts/voltgym-deploy.sh
```

This script:
- Clones the application code from the repository
- Builds and starts all Docker services
- Runs Laravel migrations and seeders
- Installs the auto-update cron

**4. Auto-update cron**

The deploy script installs a cron job that runs every 2 hours:
```
0 */2 * * * /bin/bash /opt/voltgym/infra/scripts/auto-update.sh
```

It checks the remote repository for new commits. If changes are found it pulls the code, runs migrations, rebuilds Laravel caches, and restarts the queue worker — without touching database data, WordPress uploads, or Redis data.

Update logs are written to: `/var/log/voltgym-update.log`

---

## Troubleshooting

**MySQL not starting:**
Check that port 3306 is not already in use on the host:
```bash
sudo lsof -i :3306
```
If something is using it, stop the local MySQL service:
```bash
sudo systemctl stop mysql
```

**Laravel returns 500:**
Check the application log:
```bash
docker compose exec laravel tail -n 50 storage/logs/laravel.log
```

**Permission denied on storage:**
```bash
docker compose exec laravel chmod -R 775 storage bootstrap/cache
docker compose exec laravel chown -R www-data:www-data storage bootstrap/cache
```

**Port 80 already in use (Windows):**
Windows often runs IIS on port 80. Either stop IIS or change the Nginx port in `docker-compose.yml`:
```yaml
ports:
  - "8080:80"   # Use 8080 on the host instead
```

**Reset the database without losing WordPress data:**
```bash
docker compose exec laravel php artisan migrate:fresh --seed
```
This only affects the Laravel database, not the WordPress database.

---

## Repository Structure

```
infra/
├── docker-compose.yml          — All 8 services
├── .env.example                — Environment variable template
├── docker/
│   ├── nginx/
│   │   └── default.conf        — Nginx routing (Laravel API + WordPress)
│   ├── laravel/
│   │   ├── Dockerfile          — PHP 8.2 FPM + extensions
│   │   └── php.ini             — PHP configuration
│   ├── wordpress/
│   │   └── Dockerfile          — WordPress PHP-FPM + WP-CLI + Redis
│   └── mysql/
│       └── init/
│           └── 01_create_wordpress_db.sql  — Creates WP database on first start
└── scripts/
    ├── voltgym-deploy.sh       — First-time production deployment
    ├── auto-update.sh          — Cron script (every 2h, checks for repo changes)
    ├── backup-volumes.sh       — Exports all volumes to .tar.gz archives
    └── restore-volumes.sh      — Restores volumes from a backup
```
