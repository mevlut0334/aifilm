
#!/bin/bash
set -Eeuo pipefail

echo "============================================"
echo "       AIFILM FORCE REDEPLOY"
echo "============================================"

PROJECT_DIR="/var/www/aifilm"
COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.docker"

cd "$PROJECT_DIR"

echo ""
echo "DIKKAT:"
echo "- Takip edilen yerel kod değişiklikleri silinecek."
echo "- GitHub'daki main sürümü kullanılacak."
echo "- Uygulama konteynerleri yeniden oluşturulacak."
echo "- MySQL ve Redis verileri korunacak."
echo "- .env ve .env.docker dosyaları korunacak."
echo "- Traefik durdurulmayacak."
echo ""

read -rp "Devam etmek istiyor musunuz? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "İşlem iptal edildi."
    exit 0
fi

# Gerekli dosyalar
if [ ! -f "$COMPOSE_FILE" ] || [ ! -f "$ENV_FILE" ] || [ ! -f ".env" ]; then
    echo "Gerekli dosyalar bulunamadı."
    exit 1
fi

# Git kontrolü
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Git deposu bulunamadı."
    exit 1
fi

# Traefik kontrolü
if ! docker ps --format '{{.Names}}' | grep -qx "traefik"; then
    echo "Traefik çalışmıyor. İşlem iptal edildi."
    exit 1
fi

# Proxy ağı kontrolü
if ! docker network inspect proxy >/dev/null 2>&1; then
    echo "proxy ağı bulunamadı."
    exit 1
fi

echo ""
echo "GitHub'dan güncel kod alınıyor..."

git fetch origin main
git reset --hard origin/main

echo ""
echo "Docker yapılandırması kontrol ediliyor..."

docker compose \
    --env-file "$ENV_FILE" \
    -f "$COMPOSE_FILE" \
    config --quiet

echo ""
echo "Docker imajları yeniden oluşturuluyor..."

docker compose \
    --env-file "$ENV_FILE" \
    -f "$COMPOSE_FILE" \
    up -d --build --force-recreate

echo ""
echo "Konteynerlerin hazırlanması bekleniyor..."
sleep 20

# Konteyner kontrolleri
for CONTAINER in aifilm_app aifilm_nginx aifilm_mysql aifilm_redis aifilm_queue
do
    if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
        echo "HATA: $CONTAINER çalışmıyor."
        docker logs "$CONTAINER" --tail 50 2>/dev/null || true
        exit 1
    fi
done

echo ""
echo "Storage izinleri düzenleniyor..."

docker exec aifilm_app chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

docker exec aifilm_app chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

echo ""
echo "Veritabanı migration işlemi..."

docker exec aifilm_app php artisan migrate --force

echo ""
echo "Storage link kontrol ediliyor..."

docker exec aifilm_app php artisan storage:link

echo ""
echo "Laravel optimizasyonu..."

docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan event:cache
docker exec aifilm_app php artisan view:cache

echo ""
echo "Queue worker'lara yeniden başlama sinyali gönderiliyor..."

docker exec aifilm_app php artisan queue:restart

echo ""
echo "PHP-FPM yeniden başlatılıyor..."

# PHP-FPM ve OPcache belleğini tamamen yenile
docker restart aifilm_app

echo ""
echo "Uygulamanın hazırlanması bekleniyor..."
sleep 10

if ! docker ps --format '{{.Names}}' | grep -qx "aifilm_app"; then
    echo "HATA: aifilm_app yeniden başlatılamadı."
    docker logs aifilm_app --tail 50 2>/dev/null || true
    exit 1
fi

echo ""
echo "Site kontrolü..."

APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")

if [ -z "$APP_URL" ]; then
    echo "APP_URL bulunamadı."
    exit 1
fi

for PATH_CHECK in /tr /en
do
    URL="${APP_URL%/}${PATH_CHECK}"

    if curl -fsSL --max-time 30 -o /dev/null "$URL"; then
        echo "OK: $URL"
    else
        echo "HATA: $URL erişilemiyor."
        exit 1
    fi
done

echo ""
echo "============================================"
echo "       FORCE REDEPLOY TAMAMLANDI"
echo "============================================"

docker ps --filter name=aifilm

echo ""
echo "MySQL ve Redis volume'ları silinmedi."
echo ".env dosyaları korundu."
echo "Traefik çalışmaya devam ediyor."
echo "============================================"
