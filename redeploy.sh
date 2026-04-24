#!/bin/bash
set -e

echo "===================================="
echo "🔄 AIFILM REDEPLOY (GİTHUB UPDATE)"
echo "===================================="

# Proje dizinine git
cd /var/www/aifilm

echo "📥 GitHub'dan son değişiklikler çekiliyor..."
git pull origin main

echo "🐳 Container'lar durduruluyor ve yeniden başlatılıyor..."
docker compose -f docker-compose.prod.yml --env-file .env.docker down
docker compose -f docker-compose.prod.yml --env-file .env.docker up -d --build

echo "⏳ Container'lar hazır olana kadar bekleniyor..."
sleep 15

echo "✅ Container'lar kontrol ediliyor..."
if ! docker ps | grep -q aifilm_app; then
    echo "❌ Container'lar başlatılamadı!"
    docker logs aifilm_app --tail=30
    exit 1
fi

echo "📁 Storage izinleri ayarlanıyor..."
docker exec aifilm_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec aifilm_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "🔗 Storage link kontrol ediliyor..."
docker exec aifilm_app php artisan storage:link 2>/dev/null || echo "⚠ Storage link zaten var"

echo "🗄️ Migration çalıştırılıyor..."
docker exec aifilm_app php artisan migrate --force

echo "⚡ Laravel optimize ediliyor..."
docker exec aifilm_app php artisan optimize

echo "🔄 OPcache temizleniyor..."
docker exec aifilm_app php -r "opcache_reset();" 2>/dev/null || echo "⚠ OPcache reset edilemedi"

echo ""
echo "============================================"
echo "✅ REDEPLOY BAŞARIYLA TAMAMLANDI!"
echo "============================================"
echo "🌐 Site: https://$(grep DOMAIN .env.docker | cut -d'=' -f2)"
echo "📋 Container durumu:"
docker ps --filter name=aifilm
echo "============================================"
