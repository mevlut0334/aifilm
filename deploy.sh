
#!/bin/bash
set -Eeuo pipefail

echo "============================================"
echo "       AIFILM - TRAEFIK AUTO DEPLOY"
echo "============================================"

PROJECT_DIR="/var/www/aifilm"
REPO_URL="https://github.com/mevlut0334/aifilm"
BRANCH="main"

# Root kontrolü
if [ "$EUID" -eq 0 ]; then
    echo "Bu script root kullanıcısıyla çalıştırılmamalı."
    exit 1
fi

# Docker kontrolü
if ! command -v docker >/dev/null 2>&1; then
    echo "Docker kurulu değil."
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose bulunamadı."
    exit 1
fi

# Traefik kontrolü
if ! docker ps --format '{{.Names}}' | grep -qx "traefik"; then
    echo "Traefik çalışmıyor."
    exit 1
fi

# Proxy ağı kontrolü
if ! docker network inspect proxy >/dev/null 2>&1; then
    echo "proxy Docker ağı bulunamadı."
    exit 1
fi

# Mevcut proje kontrolü
if [ -d "$PROJECT_DIR" ]; then
    echo "Proje dizini zaten mevcut: $PROJECT_DIR"
    echo "Mevcut projeyi tekrar kurmak için deploy.sh kullanma."
    exit 1
fi

# Gerekli araçlar
sudo apt-get update
sudo apt-get install -y git openssl curl

# Kullanıcı girdileri
echo ""
read -rp "Domain (örn: example.com): " DOMAIN
read -rp "Veritabanı kullanıcı adı: " DB_USERNAME
read -rsp "Resend SMTP API anahtarı: " MAIL_PASSWORD
echo ""

if [[ ! "$DOMAIN" =~ ^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    echo "Geçersiz domain."
    exit 1
fi

if [[ ! "$DB_USERNAME" =~ ^[a-zA-Z0-9_]+$ ]]; then
    echo "Geçersiz veritabanı kullanıcı adı."
    exit 1
fi

if [ -z "$MAIL_PASSWORD" ]; then
    echo "Mail API anahtarı boş bırakılamaz."
    exit 1
fi

# Veritabanı ve uygulama bilgileri
DB_NAME=$(echo "$DOMAIN" | cut -d. -f1 | tr -cd 'a-zA-Z0-9_')
DB_DATABASE="${DB_NAME}_db"

DB_ROOT_PASSWORD=$(openssl rand -hex 24)
DB_PASSWORD=$(openssl rand -hex 24)
REDIS_PASSWORD=$(openssl rand -hex 24)
APP_KEY="base64:$(openssl rand -base64 32)"

echo ""
echo "Kurulum bilgileri:"
echo "Domain: $DOMAIN"
echo "Database: $DB_DATABASE"
echo "Docker ağı: proxy"
echo "SSL: Merkezi Traefik"
echo ""

read -rp "Kuruluma devam edilsin mi? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "Kurulum iptal edildi."
    exit 0
fi

# Projeyi GitHub'dan indir
echo ""
echo "GitHub'dan güncel main kodu indiriliyor..."

sudo mkdir -p /var/www

sudo git clone \
    --branch "$BRANCH" \
    --single-branch \
    "$REPO_URL" \
    "$PROJECT_DIR"

sudo chown -R "$USER":"$USER" "$PROJECT_DIR"

cd "$PROJECT_DIR"

echo "İndirilen commit:"
git log -1 --oneline

# Gerekli dosyalar
if [ ! -f docker/nginx/traefik.conf ] || \
   [ ! -f docker-compose.prod.yml ] || \
   [ ! -f Dockerfile.prod ]; then
    echo "Gerekli Docker veya Traefik dosyaları bulunamadı."
    exit 1
fi

# Gerekli dizinler
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache

# Laravel environment
cat > .env <<EOF
APP_NAME=AiFilm
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=redis
SESSION_LIFETIME=1440
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS=noreply@${DOMAIN}
MAIL_FROM_NAME=AiFilm
EOF

# Docker environment
cat > .env.docker <<EOF
DOMAIN=${DOMAIN}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
REDIS_PASSWORD=${REDIS_PASSWORD}
APP_KEY=${APP_KEY}
MAIL_PASSWORD=${MAIL_PASSWORD}
EOF

chmod 600 .env .env.docker

# Compose kontrolü
echo ""
echo "Docker yapılandırması kontrol ediliyor..."

docker compose \
    --env-file .env.docker \
    -f docker-compose.prod.yml \
    config --quiet

# Container'ları başlat
echo ""
echo "AiFilm konteynerleri oluşturuluyor..."

docker compose \
    --env-file .env.docker \
    -f docker-compose.prod.yml \
    up -d --build

echo ""
echo "Konteynerlerin hazırlanması bekleniyor..."
sleep 20

# Container kontrolleri
for CONTAINER in aifilm_app aifilm_nginx aifilm_mysql aifilm_redis aifilm_queue
do
    if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
        echo "HATA: $CONTAINER başlatılamadı."
        docker logs "$CONTAINER" --tail 50 2>/dev/null || true
        exit 1
    fi
done

# Storage izinleri
echo ""
echo "Storage izinleri düzenleniyor..."

docker exec aifilm_app chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

docker exec aifilm_app chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Laravel hazırlıkları
echo ""
echo "Laravel kurulumu hazırlanıyor..."

docker exec aifilm_app php artisan migrate --force
docker exec aifilm_app php artisan db:seed --force
docker exec aifilm_app php artisan storage:link

# Laravel önbellekleri
echo ""
echo "Laravel önbellekleri hazırlanıyor..."

docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan event:cache
docker exec aifilm_app php artisan view:cache

# PHP-FPM ve OPcache yenile
echo ""
echo "PHP-FPM yeniden başlatılıyor..."

docker restart aifilm_app

sleep 10

if ! docker ps --format '{{.Names}}' | grep -qx "aifilm_app"; then
    echo "HATA: aifilm_app yeniden başlatılamadı."
    docker logs aifilm_app --tail 50 2>/dev/null || true
    exit 1
fi

# HTTP kontrolleri
echo ""
echo "Web sitesi kontrolleri yapılıyor..."

APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
APP_URL="${APP_URL%/}"

if [ -z "$APP_URL" ]; then
    echo "APP_URL bulunamadı."
    exit 1
fi

for PATH_CHECK in /tr /en
do
    URL="${APP_URL}${PATH_CHECK}"

    if curl -fsSL --max-time 30 -o /dev/null "$URL"; then
        echo "OK: $URL"
    else
        echo "HATA: $URL erişilemiyor."
        exit 1
    fi
done

echo ""
echo "============================================"
echo "       AIFILM KURULUMU TAMAMLANDI"
echo "============================================"

echo "Web sitesi: ${APP_URL}"
echo "SSL: Merkezi Traefik tarafından yönetiliyor"
echo "PHP-FPM: Yeniden başlatıldı"
echo "OPcache: Yeni PHP-FPM süreciyle yenilendi"
echo ""

docker ps --filter name=aifilm

echo "============================================"
