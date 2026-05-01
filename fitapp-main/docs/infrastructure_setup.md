# Volt Gym Infrastructure Setup

This document serves as the **comprehensive reference** for the operational and deployment architecture of the Volt Gym application. It dictates how the entire ecosystem—composed of a Laravel backend, a WordPress frontend, and supporting services—is managed, executed, and maintained across environments.

## Architectural Principles

The operational design rigorously enforces two core engineering methodologies:

1. **KISS (Keep It Simple, Stupid)**
   The infrastructure abandons complex remote-copying or hyper-layered CI/CD artifacts in favor of native Docker functionality. By unifying local development and remote server deployments around Git cloning combined with immediate Docker Compose build execution (Bind Mounts), the cognitive load on system administrators is drastically reduced. 

2. **SOLID Principles in DevOps**
   While traditionally applied to object-oriented programming, these concepts heavily influenced the layout of the `voltgym-infra` containerized suite:
   - **Single Responsibility Principle:** Every container serves only one role (e.g., the queue worker only handles Redis jobs, the API exclusively serves data, the Nginx instance delegates routing).
   - **Open/Closed Principle:** The Docker orchestration can be extended (open) to include new services without modifying the existing stable configurations (closed).
   - **Interface Segregation:** Dependencies are isolated (Laravel has its own definition, WordPress has its own PHP stack) rather than sharing monolithic components. 

## The "Plug and Play" Methodology

The deployment strategy provides a **Plug and Play** experience, ensuring that whether a developer operates on a standard Windows host, or the project is being instantiated on a production Ubuntu Linux Remote, the setup commands are uniformly minimal.

### Global Setup Instructions

From the moment the directory is downloaded from GitHub, no more than three steps are required to achieve a live stack:

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/ieszaidinvergeles/fitapp.git
   cd fitapp/voltgym-infra
   ```
2. **Execute Deployment Script (Linux)** *OR* **Compose Native (Windows):**
   ```bash
   ./scripts/voltgym-deploy.sh
   # OR on Windows purely rely on:
   docker compose up -d --build
   ```

*Note on Local Execution:* The new architecture employs **Relative Bind Mounts**. This means Docker directly reads `../server` and `../wordpress` as live host volumes. If a developer edits a PHP file locally, it updates instantly within the container without requiring image reconstruction.

## Docker Compose ecosystem

The core of the orchestration relies on `docker-compose.yml`. Each service definitions encapsulates a specific domain:

### Nginx (`voltgym_nginx`)
- Acts as the reverse proxy for both Laravel API and the WordPress Theme.
- Serves API routes locally forwarding port `80` to Laravel, and port `8000` to WordPress.

### Laravel API (`voltgym_laravel`)
- Powered by `php:8.2-fpm-alpine`.
- Features an automated **entrypoint (`entrypoint.sh`)**. Upon container birth, this script dynamically evaluates if dependencies (`vendor` directory via Composer) or environment mappings (`.env` file) exist. If missing, it natively resolves them. 

### MySQL (`voltgym_mysql`)
- Operates on version `8.0` and orchestrates identical statefulness securely mapped to `mysql_data`.
- Facilitates connections internally preventing unallowed external bounds.

### Adminer (`voltgym_adminer`)
- The internal database schema navigator exposed on port `8080`.
- Offers immediate visibility into the Laravel Models mapping and WordPress schema tables.

### Exposed Port Mappings (Firewall Target Settings)
For cloud systems like Azure or AWS, the following Network Security Group (NSG) Inbound Rules correspond directly to the exposed Docker ports:
- **Port 80 (HTTP):** WordPress Public Web
- **Port 8000 (HTTP):** Laravel API Services
- **Port 8080 (HTTP):** Adminer Visual DB GUI
- **Port 3306 (TCP - Optional):** Exposes MySQL directly for desktop tools like DBeaver or DataGrip.
*Note: Ports without an explicit binding in Compose (like Redis 6379) are securely isolated within the `voltgym_net` stack.*

### Background Runners 
- **Queue Work:** Continuously loops listening to the Redis cache interface.
- **Scheduler:** Manages continuous cron task dispatches directly defined within Laravel's kernel structure.

## Remote Maintenance & Automation (Zero-Downtime Updates)

A system daemon operates via Cron linking against `scripts/auto-update.sh`. It evaluates standard git hashes against `origin/main` automatically, securing rolling updates directly to the application without manual intervention while preserving absolute data sovereignty across stateful containers.

**How the update operates:**
1. Triggers Git Pull overriding any stale cache on standard bind mounts (Theme Files magically sync natively).
2. Executes `composer install` inside the Laravel container explicitly parsing new APIs.
3. Automatically fires `php artisan migrate --force`. This handles new tables and columns perfectly **without** dropping existing data, protecting WordPress rows and user tables.

**Implementation Script (Linux Host):**
To ensure the application dynamically updates every two hours natively, instantiate the following command into your `crontab -e`:
```bash
0 */2 * * * cd /home/voltgym/fitapp/voltgym-infra && ./scripts/auto-update.sh >> /var/log/voltgym-auto-update.log 2>&1
```

## System Resilience

Under normal circumstances involving server reboots, scaling downs, or hardware resets, absolutely **no manual intervention is required**. All Compose services explicitly depend on the `restart: unless-stopped` directive. Docker's Engine automatically boots the final live state of the containers asynchronously immediately after Host OS initialization.
