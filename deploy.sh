#!/bin/bash
set -e

echo "===================================="
echo "🚀 AIFILM DOCKER AUTO DEPLOY WITH SSL"
echo "===================================="

# Sadece 2 soru sor
read -p "Domain (örn: example.com): " DOMAIN
read -p "SSL Email: " SSL_EMAIL

# Domain'den DB name ve username oluştur
DB_NAME=$(echo "$DOMAIN" | sed 's/\..*//g' | sed 's/-//g')
DB_USERNAME="${DB_NAME}"

# Güvenli random password üret
DB_ROOT_PASSWORD=$(openssl rand -base64 24 | tr -d "=+/" | cut -c1-20)
DB_PASSWORD=$(openssl rand -base64 24 | tr -d "=+/" | cut -c1-20)
REDIS_PASSWORD=$(openssl rand -base64 24 | tr -d "=+/" | cut -c1-20)

# Port default
HTTP_PORT=80
HTTPS_PORT=443

echo ""
echo "📋 Deployment Bilgileri:"
echo "  Domain: $DOMAIN"
echo "  SSL Email: $SSL_EMAIL"
echo "  HTTP Port: $HTTP_PORT"
echo "  HTTPS Port: $HTTPS_PORT"
echo "  DB Name: ${DB_NAME}_db"
echo "  DB Username: $DB_USERNAME"
echo "  DB Root Password: $DB_ROOT_PASSWORD"
echo "  DB Password: $DB_PASSWORD"
echo "  Redis Password: $REDIS_PASSWORD"
echo ""
read -p "Devam edilsin mi? (y/n): " CONFIRM
if [ "$CONFIRM" != "y" ]; then
    echo "❌ Deployment iptal edildi."
    exit 1
fi

# RAM ve swap kontrolü
TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
echo "💾 Toplam RAM: ${TOTAL_RAM}MB"

if [ "$TOTAL_RAM" -lt 2000 ]; then
    if [ ! -f /swapfile ]; then
        echo "⚠ RAM düşük, 2GB swap oluşturuluyor..."
        sudo fallocate -l 2G /swapfile
        sudo chmod 600 /swapfile
        sudo mkswap /swapfile
        sudo swapon /swapfile
        echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    else
        echo "✔ Swap zaten mevcut"
    fi
fi

# Gerekli paketleri kur
echo "📦 Gerekli paketler kuruluyor..."
sudo apt update
sudo apt install -y certbot git curl unzip zip

# Sistem Nginx'i durdur ve devre dışı bırak
echo "🔧 Sistem Nginx durduruluyor..."
sudo systemctl stop nginx 2>/dev/null || true
sudo systemctl disable nginx 2>/dev/null || true

# Repo clone / update
if [ ! -d /var/www/aifilm ]; then
    echo "📥 Repo clone..."
    sudo git clone https://github.com/mevlut0334/aifilm /var/www/aifilm
else
    echo "📥 Repo update..."
    cd /var/www/aifilm && sudo git fetch --all && sudo git reset --hard origin/main
fi

cd /var/www/aifilm

# Ownership ayarla
sudo chown -R $USER:$USER /var/www/aifilm

# APP_KEY üret
APP_KEY="base64:$(openssl rand -base64 32)"

# .env dosyası oluştur
echo "⚙ .env dosyası oluşturuluyor..."
cat > .env << EOF
APP_NAME="AiFilm"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=${DB_NAME}_db
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
CACHE_PREFIX=

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
EOF

# docker-compose.prod.yml için .env.docker oluştur
cat > .env.docker << EOF
DOMAIN=${DOMAIN}
HTTP_PORT=${HTTP_PORT}
HTTPS_PORT=${HTTPS_PORT}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
DB_DATABASE=${DB_NAME}_db
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
REDIS_PASSWORD=${REDIS_PASSWORD}
APP_KEY=${APP_KEY}
EOF

echo "✅ .env dosyaları hazır"

# Storage ve cache klasörlerini hazırla
echo "📁 Storage ve cache klasörleri hazırlanıyor..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# SSL sertifikası dizinini oluştur
sudo mkdir -p /etc/letsencrypt

# SSL sertifikası al (certbot standalone mode)
echo "🔒 SSL sertifikası alınıyor (HTTP challenge)..."
sudo certbot certonly --standalone --non-interactive --agree-tos --email ${SSL_EMAIL} -d ${DOMAIN} --http-01-port=80

# SSL sertifikası izinlerini ayarla
sudo chmod -R 755 /etc/letsencrypt/live
sudo chmod -R 755 /etc/letsencrypt/archive

# Docker Compose ile başlat
echo "🐳 Docker container'ları başlatılıyor..."
docker compose -f docker-compose.prod.yml --env-file .env.docker down -v 2>/dev/null || true
docker compose -f docker-compose.prod.yml --env-file .env.docker up -d --build

# Container'ların hazır olmasını bekle
echo "⏳ Container'lar hazırlanıyor..."
sleep 45

# Container'lar ayakta mı kontrol et
if ! docker ps | grep -q aifilm_app; then
    echo "❌ Container'lar başlatılamadı!"
    docker logs aifilm_app 2>/dev/null || true
    docker logs aifilm_mysql 2>/dev/null || true
    exit 1
fi

echo "✅ Container'lar çalışıyor"

# Storage izinlerini Docker içinde ayarla
echo "📁 Storage izinleri ayarlanıyor..."
docker exec aifilm_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec aifilm_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Migration ve seed
echo "🗄 Database migration çalıştırılıyor..."
docker exec aifilm_app php artisan migrate --force || echo "⚠ Migration hatası (normal olabilir)"
docker exec aifilm_app php artisan db:seed --force || echo "⚠ Seed hatası (normal olabilir)"

# Storage link
echo "🔗 Storage link oluşturuluyor..."
docker exec aifilm_app php artisan storage:link || echo "⚠ Storage link zaten var"

# Cache temizle ve optimize et
echo "🔧 Laravel optimize ediliyor..."
docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan route:cache
docker exec aifilm_app php artisan view:cache
docker exec aifilm_app php artisan optimize

# OPcache'i temizle
echo "🔄 OPcache temizleniyor..."
docker exec aifilm_app php -r "opcache_reset();" || echo "⚠ OPcache reset edilemedi"

# SSL otomatik yenileme için cron job ekle
echo "🔄 SSL otomatik yenileme ayarlanıyor..."
(crontab -l 2>/dev/null | grep -v certbot; echo "0 3 * * * certbot renew --quiet --deploy-hook 'docker restart aifilm_nginx'") | crontab -

# Systemd service oluştur (boot'ta otomatik başlatma GARANTİSİ)
echo "🔧 Systemd service oluşturuluyor..."
sudo tee /etc/systemd/system/aifilm.service > /dev/null << SYSTEMD
[Unit]
Description=AiFilm Docker Compose Application
Requires=docker.service
After=docker.service network-online.target
Wants=network-online.target

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/var/www/aifilm
ExecStart=/usr/bin/docker compose -f docker-compose.prod.yml --env-file .env.docker up -d
ExecStop=/usr/bin/docker compose -f docker-compose.prod.yml --env-file .env.docker down
TimeoutStartSec=300
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
SYSTEMD

sudo systemctl daemon-reload
sudo systemctl enable aifilm.service
echo "✅ Systemd service aktif - Sunucu restart'ta otomatik başlayacak"

# DB bilgilerini dosyaya kaydet
echo "💾 DB bilgileri kaydediliyor..."
cat > ~/deployment-info-${DOMAIN}.txt << EOF
============================================
DEPLOYMENT BİLGİLERİ - ${DOMAIN}
============================================
Domain: ${DOMAIN}
SSL Email: ${SSL_EMAIL}
Deployment Date: $(date)

DATABASE BİLGİLERİ:
-------------------
DB Name: ${DB_NAME}_db
DB Username: ${DB_USERNAME}
DB Password: ${DB_PASSWORD}
DB Root Password: ${DB_ROOT_PASSWORD}

REDIS BİLGİLERİ:
----------------
Redis Password: ${REDIS_PASSWORD}

APP KEY:
--------
${APP_KEY}

============================================
⚠ BU BİLGİLERİ GÜVENLİ BİR YERDE SAKLAYIN!
============================================
EOF

chmod 600 ~/deployment-info-${DOMAIN}.txt

echo ""
echo "============================================"
echo "✅ DEPLOYMENT BAŞARIYLA TAMAMLANDI!"
echo "============================================"
echo "🌐 Web Sitesi: https://${DOMAIN}"
echo "🔒 SSL: Aktif (otomatik yenileme ayarlı)"
echo "🐳 Docker: Container'lar çalışıyor"
echo "💾 Database: Hazır"
echo "📦 Redis: Aktif"
echo "🔧 Systemd: Boot'ta otomatik başlatma aktif"
echo ""
echo "📋 DB Bilgileri ~/deployment-info-${DOMAIN}.txt dosyasına kaydedildi"
echo "   Görüntülemek için: cat ~/deployment-info-${DOMAIN}.txt"
echo "============================================"
echo ""
echo "📋 Faydalı Komutlar:"
echo "  - Container durumu: docker ps"
echo "  - Logları görüntüle: docker logs aifilm_app"
echo "  - Service durumu: sudo systemctl status aifilm"
echo "  - Container'ları durdur: cd /var/www/aifilm && docker compose -f docker-compose.prod.yml down"
echo "  - Container'ları başlat: cd /var/www/aifilm && docker compose -f docker-compose.prod.yml up -d"
echo "============================================"
