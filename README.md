# FitApp (Volt Gym) ⚡🏋️‍♂️

Welcome to the FitApp project overview. This repository holds the complete suite for the **Volt Gym** platform, integrating a robust **Laravel API backend**, a customized **WordPress Frontend**, and a modern suite of containerized infrastructure ensuring high availability and seamless developer experience.

## Project Structure

```text
fitapp/
  server/          - Laravel backend (REST API, logic, Migrations)
  wordpress/       - WordPress frontend (Custom Theme & Plugins)
  voltgym-infra/   - Docker Orchestration and Deployment scripts
  docs/            - Cross-cutting architectural documentation
```

## 🛠️ Installation Guide (Plug & Play)

The infrastructure follows strict SOLID and KISS principles, designed so anybody can spin it up with zero manual configuration. Everything is orchestrated using **Relative Bind Mounts**.

### Option A: Hosting on Linux (Ubuntu Server)
This is for the remote production server.

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
   *(The script handles `.env` creation automatically).*

3. **Persistent Reboots:** The containers are configured with `restart: unless-stopped`. They will automatically boot up safely if the Ubuntu host is ever restarted.

### Option B: Hosting on Windows (Local Development)
This is for developers iterating locally.

1. **Clone the repository and enter the infra folder:**
   ```powershell
   git clone https://github.com/ieszaidinvergeles/fitapp.git
   cd fitapp/voltgym-infra
   ```
2. **Create the Environment File:**
   Copy `.env.example` to `.env` and fill in the dummy passwords. (You can leave default dummy values).
   ```powershell
   copy .env.example .env
   ```
3. **Execute Docker Compose Build:**
   ```powershell
   docker compose up -d --build
   ```

*Note: For Windows, the code in `server/laravel` and `wordpress/wordpress-theme` is bind-mounted directly. Edits in your Windows IDE will instantly reflect on the running containers!*

---

## 🌩️ Cloud Firewall Settings & Access (Azure / AWS)

If deploying to a Cloud VM, you **MUST** ensure the following inbound ports are open in your **Network Security Group (NSG)** or VPC Firewall to make the application accessible from the outside.

| Port | Protocol | Purpose / Location |
|------|----------|-------------------|
| **22** | TCP | SSH Server Access |
| **80** | TCP | **WordPress Site** (Main User Interface) |
| **8000** | TCP | **Laravel API** (Direct Backend Access) |
| **8080** | TCP | **Adminer** (Database Graphical Interface) |
| **3306** | TCP | MySQL Direct Connection (For DBeaver, DataGrip) |

*If you do not open port `8080`, Adminer will not load on the browser, even if Docker is working internally!*

---

## 🤖 2-Hour Auto-Update Automation (CD Pipeline)

We built an internal Continuous Deployment logic that doesn't rely on Github Actions, heavily minimizing external failure points.

The file `voltgym-infra/scripts/auto-update.sh` periodically checks if Github's `main` branch has new commits (e.g. a new migration or a new changed view). 
If it discovers changes, it:
1. Pulls the latest code natively.
2. Updates Composer dependecies silently.
3. Automatically runs logic changes `php artisan migrate --force` to inject new database schema rows **WITHOUT destroying user WordPress or Database data**.

### How to activate it (Linux Host Setup)
Run this command from your Linux server shell to install it in the standard `cron`:

```bash
echo "0 */2 * * * cd /home/voltgym/fitapp/voltgym-infra && ./scripts/auto-update.sh >> /var/log/voltgym-auto-update.log 2>&1" | crontab -
```
This tells the Ubuntu host to execute the script every exactly 2 hours permanently while the server is alive.

---

## Technical Documentation
For granular details regarding documentation conventions, versioning tags, and architectural specs, refer to:
- `docs/infrastructure_setup.md`
- `server/docs/`
- `wordpress/docs/`
