
# 🎬 AiFilm

AI-powered film creation and video production platform built with Laravel 11.

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
- Laravel 11
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

---

## 🔄 Deployment Scripts

### 1. Initial Installation: deploy.sh

Use when installing AiFilm for the first time on a new server.

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
- Register the domain with Traefik.
- Run Laravel migrations.

Do not use this script to reinstall an existing production deployment.

### 2. Normal Update: redeploy.sh

Use for normal application updates after pushing code to GitHub.

```bash
cd /var/www/aifilm
./redeploy.sh
```

Responsibilities:

- Pull latest GitHub changes.
- Validate Docker Compose configuration.
- Build updated application images.
- Update required containers.
- Run database migrations.
- Optimize Laravel.

The script does not intentionally stop Traefik or delete database volumes.

### 3. Emergency Update: force-redeploy.sh

Use when you need to restore the tracked project files to the latest GitHub version.

```bash
cd /var/www/aifilm
./force-redeploy.sh
```

This script:

- Resets tracked local code changes.
- Gets the latest main branch.
- Recreates application containers.
- Runs migrations and Laravel optimization.

**Warning:** Local tracked code changes will be discarded.

Database and Redis volumes must remain protected.

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

### 2. Push changes to GitHub

```bash
git add .
git commit -m "Feature update"
git push origin main
```

### 3. Connect to the server

```bash
ssh administrator@SERVER_IP
```

### 4. Update AiFilm

```bash
cd /var/www/aifilm
./redeploy.sh
```

### 5. Verify the deployment

```bash
docker ps
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
| Backend | Laravel 11 |
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
