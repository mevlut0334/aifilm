#!/bin/bash
set -e

echo "============================================"
echo "⚠️  FORCE REDEPLOY (LOCAL DEĞİŞİKLİKLERİ SİLER!)"
echo "============================================"

# Proje dizinine git
cd /var/www/aifilm

echo "⚠️  Bu işlem:"
echo "  - Local tüm değişiklikleri silecek"
echo "  - GitHub'dan temiz kod çekecek"
echo "  - Container'ları tamamen yeniden oluşturacak"
echo ""
read -p "Devam edilsin mi? (y/n): " CONFIRM

if [ "$CONFIRM" != "y" ]; then
    echo "❌ Force redeploy iptal edildi."
    exit 0
fi

echo ""
echo "🗑️ Local değişiklikler siliniyor..."
git fetch --all
git reset --hard origin/main
git clean -fd

echo "🐳 Container'lar durduruluyor (volumes dahil)..."
docker compose -f docker-compose.prod.yml --env-file .env.docker down -v

echo "🏗️ Container'lar sıfırdan oluşturuluyor..."
docker compose -f docker-compose.prod.yml --env-file .env.docker up -d --build

echo "⏳ Container'lar hazır olana kadar bekleniyor (45 saniye)..."
sleep 45

echo "✅ Container'lar kontrol ediliyor..."
if ! docker ps | grep -q aifilm_app; then
    echo "❌ Container'lar başlatılamadı!"
    docker logs aifilm_app --tail=30
    docker logs aifilm_mysql --tail=30
    exit 1
fi

echo "📁 Storage izinleri ayarlanıyor..."
docker exec aifilm_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec aifilm_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "🗄️ Migration çalıştırılıyor..."
docker exec aifilm_app php artisan migrate --force

echo "🔗 Storage link oluşturuluyor..."
docker exec aifilm_app php artisan storage:link 2>/dev/null || echo "⚠ Storage link zaten var"

echo "⚡ Laravel optimize ediliyor..."
docker exec aifilm_app php artisan optimize

echo "🔄 OPcache temizleniyor..."
docker exec aifilm_app php -r "opcache_reset();" 2>/dev/null || echo "⚠ OPcache reset edilemedi"

echo ""
echo "============================================"
echo "✅ FORCE REDEPLOY BAŞARIYLA TAMAMLANDI!"
echo "============================================"
echo "🌐 Site: https://$(grep DOMAIN .env.docker | cut -d'=' -f2)"
echo "📋 Container durumu:"
docker ps --filter name=aifilm
echo ""
echo "⚠️  Not: Database volume'ları korundu, verileriniz güvende."
echo "============================================"
