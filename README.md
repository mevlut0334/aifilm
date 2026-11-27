
# 🎬 AiFilm

AI-powered film creation and video production platform built with Laravel.

AiFilm transforms written scenarios into cinematic video content using AI technologies.

---

## 🚀 Production Deployment

AiFilm production infrastructure uses Docker Compose and a centralized Traefik reverse proxy.

### Infrastructure

- Ubuntu 24.04 LTS
- Docker Engine
- Docker Compose
- Traefik v3
- Nginx
- PHP 8.3 / PHP-FPM
- Laravel
- MySQL 8
- Redis

### Architecture

```text
                   Internet
                       |
                  Domain / DNS
                       |
                  Traefik Proxy
                   Ports 80/443
                       |
                  HTTPS / SSL
                       |
                 Docker Network
                     proxy
                       |
                  AiFilm Nginx
                       |
                  PHP-FPM App
                       |
              -----------------
              |               |
            MySQL            Redis
              |               |
          Database          Cache
                            Queue
```

### Centralized Traefik

Traefik is installed once on the server and manages all production projects.

- HTTP and HTTPS traffic is handled centrally.
- SSL certificates are managed by Let's Encrypt through Traefik.
- SSL renewal is handled automatically by Traefik.
- Individual projects do not bind host ports 80 or 443.
- Projects connect to the external Docker network named `proxy`.
- Each project defines its own domain using Traefik labels.

**Important:** Never install another reverse proxy that attempts to use host ports 80 or 443.

---

## 🆕 First Installation

### Requirements

Before installing AiFilm, the server must have:

1. Ubuntu 24.04 or compatible Linux.
2. Docker Engine.
3. Docker Compose plugin.
4. Centralized Traefik running.
5. External Docker network named `proxy`.
6. Domain DNS pointing to the server.
7. Ports 80 and 443 accessible.

### DNS Configuration

Create an A record:

| Type | Name | Value |
|---|---|---|
| A | @ | SERVER_IP |
| A | www | SERVER_IP (optional) |

### Installation

Clone the repository:

```bash
git clone https://github.com/mevlut0334/aifilm.git
cd aifilm
```

Make the deployment script executable:

```bash
chmod +x deploy.sh
```

Run the installation:

```bash
./deploy.sh
```

The script asks for:

1. Domain name.
2. Database username.
3. Resend SMTP API key.

It generates database passwords, Redis password and Laravel APP_KEY automatically.

Traefik handles SSL certificate issuance and renewal.

The initial installation script:

- Checks Docker and Docker Compose.
- Checks Traefik and the external proxy network.
- Clones the latest `main` branch from GitHub.
- Generates production environment files.
- Starts application containers.
- Runs Laravel migrations and initial seeders.
- Creates Laravel storage links.
- Builds Laravel configuration, event and view caches.
- Restarts PHP-FPM to reload application code and OPcache.
- Verifies Turkish and English website routes.

**Do not use this script to update an existing production deployment.**

---

## 🔄 Deployment Scripts

AiFilm provides three deployment scripts.

| Situation | Script | Purpose |
|---|---|---|
| First installation on a new server | `deploy.sh` | Initial setup |
| Normal application update | `redeploy.sh` | Update from GitHub |
| Emergency recovery | `force-redeploy.sh` | Force synchronization with GitHub |

### 1. Initial Installation: deploy.sh

Use only when installing AiFilm for the first time on a new server.

```bash
./deploy.sh
```

Responsibilities:

- Check Docker and Compose.
- Check Traefik availability.
- Check the external proxy network.
- Clone the repository.
- Generate environment files.
- Configure database and Redis.
- Start application containers.
- Run Laravel migrations and initial seeders.
- Prepare Laravel caches.
- Restart PHP-FPM.
- Verify `/tr` and `/en` routes.

Do not use this script to reinstall an existing production deployment.

### 2. Normal Update: redeploy.sh

Use for normal application updates after pushing code to GitHub.

```bash
cd /var/www/aifilm
./redeploy.sh
```

Responsibilities:

- Fetch the latest GitHub `main` branch.
- Pull changes using fast-forward only.
- Validate Docker Compose configuration.
- Build updated application images.
- Update application containers.
- Run database migrations.
- Prepare Laravel configuration, event and view caches.
- Notify queue workers to restart.
- Restart the application container and PHP-FPM.
- Reload PHP OPcache through a fresh PHP-FPM process.
- Verify `/tr` and `/en` routes.

This is the recommended deployment method for everyday updates.

**You do not need to run `git pull` separately on the server.** The script fetches and pulls the latest GitHub code automatically.

### 3. Emergency Update: force-redeploy.sh

Use when tracked local server changes must be discarded and the latest GitHub `main` branch restored.

```bash
cd /var/www/aifilm
./force-redeploy.sh
```

Responsibilities:

- Fetch the latest GitHub `main` branch.
- Reset tracked files to `origin/main`.
- Rebuild and recreate application containers.
- Run database migrations.
- Prepare Laravel caches.
- Notify queue workers to restart.
- Restart PHP-FPM and reload OPcache.
- Verify `/tr` and `/en` routes.

**Warning:** This script permanently discards tracked local changes on the server.

It does not intentionally delete MySQL or Redis Docker volumes or production environment files.

---

## ⚙️ PHP OPcache and PHP-FPM

Production PHP configuration uses OPcache for performance.

The production configuration may use:

```ini
opcache.enable = 1
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
```

When `opcache.validate_timestamps=0`, PHP-FPM does not automatically detect changed PHP files.

This means PHP-FPM may continue executing previously cached PHP code after a deployment.

For this reason, the deployment scripts restart the application container after updating code and preparing Laravel caches.

A fresh PHP-FPM process reloads the application code and creates a fresh OPcache state.

**Important:** Running `opcache_reset()` through PHP CLI does not guarantee that the PHP-FPM OPcache is reset. CLI and PHP-FPM may use separate OPcache processes.

Both `redeploy.sh` and `force-redeploy.sh` automatically restart `aifilm_app`.

---

## 📋 Deployment Verification

After deployment, the scripts verify:

- Application containers are running.
- Turkish homepage `/tr` returns HTTP 200.
- English homepage `/en` returns HTTP 200.

For additional manual verification:

```bash
docker ps --filter name=aifilm
```

Check website routes:

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

Check application logs:

```bash
docker logs aifilm_app --tail 100
```

Check Nginx logs:

```bash
docker logs aifilm_nginx --tail 100
```

---

## 📁 Environment Files

| File | Purpose | Git |
|---|---|---|
| `.env` | Laravel application configuration | Ignored |
| `.env.docker` | Docker Compose variables | Ignored |
| `.env.example` | Example environment template | Tracked |

Production environment files are generated or maintained on the server.

Never commit real passwords, API keys or APP_KEY values.

### Important Environment Variables

- `DOMAIN`
- `APP_KEY`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `REDIS_PASSWORD`
- `MAIL_PASSWORD`

---

## 🔄 Daily Development Workflow

### 1. Develop locally

Work on the application using your local development environment.

Test your changes before deploying them.

### 2. Push changes to GitHub

```bash
git add .
git commit -m "Application update"
git push origin main
```

### 3. Connect to the production server

```bash
ssh administrator@SERVER_IP
```

### 4. Update AiFilm

```bash
cd /var/www/aifilm
./redeploy.sh
```

The deployment script automatically fetches the latest GitHub `main` branch.

A separate `git pull` is not required.

### 5. Verify deployment

```bash
docker ps --filter name=aifilm
```

Check the website:

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

Check application logs:

```bash
docker logs aifilm_app --tail 100
```

Website:

https://asilov.com

---

## 🐳 Docker Management

### View containers

```bash
docker ps
```

### View AiFilm containers

```bash
docker ps --filter name=aifilm
```

### Application logs

```bash
docker logs -f aifilm_app
```

### Queue logs

```bash
docker logs -f aifilm_queue
```

### Nginx logs

```bash
docker logs -f aifilm_nginx
```

### Traefik logs

```bash
docker logs -f traefik
```

### Restart AiFilm Nginx

```bash
docker compose --env-file .env.docker -f docker-compose.prod.yml restart nginx
```

### Restart application container

```bash
docker restart aifilm_app
```

Do not use `docker compose down` for routine updates.

Never use `docker compose down -v` on production.

---

## 🌐 Adding New Projects to the Server

The server supports multiple independent Docker projects through centralized Traefik.

For each new project:

1. Create its own project directory.
2. Create its own Docker Compose configuration.
3. Connect its reverse proxy service to the external `proxy` network.
4. Add Traefik routing labels.
5. Configure the domain DNS.
6. Let Traefik obtain and manage its SSL certificate.

Example Docker network configuration:

```yaml
networks:
  proxy:
    external: true
    name: proxy
```

Example Traefik labels:

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.docker.network=proxy"
  - "traefik.http.routers.myapp.rule=Host(`example.com`)"
  - "traefik.http.routers.myapp.entrypoints=websecure"
  - "traefik.http.routers.myapp.tls=true"
  - "traefik.http.routers.myapp.tls.certresolver=letsencrypt"
  - "traefik.http.services.myapp.loadbalancer.server.port=80"
```

Each project must use unique:

- Container names
- Router names
- Service names
- Database names
- Docker volumes

Projects must not publish host ports 80 or 443.

---

## 💻 Local Development

Local development continues to use the standard Docker Compose configuration.

Clone the repository:

```bash
git clone https://github.com/mevlut0334/aifilm.git
cd aifilm
```

Install dependencies:

```bash
composer install
npm install
```

Configure Laravel:

```bash
cp .env.example .env
php artisan key:generate
```

Start local containers:

```bash
docker compose up -d
```

Run migrations:

```bash
docker exec aifilm_app php artisan migrate --seed
```

Build frontend assets:

```bash
npm run dev
```

**Important:**

- Local development: `docker-compose.yml`
- Production deployment: `docker-compose.prod.yml`

Local development must not depend on the production Traefik network.

---

## 🛠️ Tech Stack

| Component | Technology |
|---|---|
| Backend | Laravel |
| PHP | PHP 8.3 |
| Frontend | Blade, Vite, Tailwind CSS |
| Database | MySQL 8 |
| Cache | Redis |
| Web Server | Nginx |
| Application Server | PHP-FPM |
| Reverse Proxy | Traefik |
| SSL | Let's Encrypt |
| Containers | Docker |
| Version Control | Git / GitHub |

---

## 📦 Features

- AI-powered film creation
- Scenario-based video generation
- User accounts and subscriptions
- Video production management
- User dashboard
- Admin dashboard
- RESTful API
- Background queue processing
- Redis caching
- MySQL database

---

## ⚠️ Important Production Rules

1. Never remove the external `proxy` network.
2. Never bind project containers directly to host ports 80 or 443.
3. Never delete production database volumes during deployment.
4. Never use `docker compose down -v` in production.
5. Never commit `.env` or `.env.docker`.
6. Do not modify centralized Traefik configuration when updating AiFilm.
7. Use `redeploy.sh` for normal updates.
8. Use `force-redeploy.sh` only when necessary.
9. Keep database backups before major migrations.
10. Restart PHP-FPM after deploying PHP code when OPcache timestamp validation is disabled.
11. Verify `/tr` and `/en` return HTTP 200 after deployment.
12. Never run the initial installation script on an existing production installation.

---

## 📁 Project Structure

```text
aifilm/
├── deploy.sh
├── redeploy.sh
├── force-redeploy.sh
├── docker-compose.yml
├── docker-compose.prod.yml
├── Dockerfile
├── Dockerfile.prod
├── docker/
│   ├── nginx/
│   │   ├── prod.conf.template
│   │   └── traefik.conf
│   └── php/
│       ├── php.ini
│       └── php-fpm.conf
├── DEPLOYMENT.md
├── INSTALL.md
├── REDEPLOY-GUIDE.md
└── README.md
```

---

## 📚 Documentation

- [Deployment Guide](DEPLOYMENT.md)
- [Installation Guide](INSTALL.md)
- [Redeployment Guide](REDEPLOY-GUIDE.md)

---

## 📄 License

This project is proprietary software.

All rights reserved.
