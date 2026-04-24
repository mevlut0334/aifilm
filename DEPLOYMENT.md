# 🚀 AiFilm - Production Deployment Kılavuzu

## 📦 Hazırlanan Dosyalar

Bu deployment sistemi size **production-ready**, **SSL destekli**, **auto-scaling** bir Laravel kurulumu sağlar.

### Yeni Dosyalar:
```
deploy.sh                          # Ana deployment scripti
redeploy.sh                        # GitHub'dan güncelleme scripti
docker-compose.prod.yml            # Production Docker Compose config
Dockerfile.prod                    # Production optimized Dockerfile
docker/php/php.ini                 # PHP production config (OPcache + JIT)
docker/php/php-fpm.conf           # PHP-FPM worker config
docker/nginx/prod.conf.template    # Nginx SSL config
INSTALL.md                         # Detaylı kurulum kılavuzu
```

---

## ⚡ Hızlı Başlangıç

### Sunucuda (Ubuntu 20.04+):

```bash
# 1. Docker'ı kur
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt-get install docker-compose-plugin -y
sudo usermod -aG docker $USER
newgrp docker

# 2. Repository'yi clone'la
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
cd /var/www
git clone https://github.com/mevlut0334/aifilm.git aifilm
cd aifilm

# 3. Scripti çalıştır
chmod +x deploy.sh
./deploy.sh
```

**İki soru soracak:**
1. Domain adı (örn: `aifilm.com`)
2. SSL email (örn: `admin@aifilm.com`)

Geri kalan her şey otomatik! ✨

---

## 🎯 Özellikler

### ✅ Güvenlik
- ✅ SSL/TLS otomatik (Let's Encrypt)
- ✅ Güvenli random şifreler (DB, Redis)
- ✅ Güvenlik headers (HSTS, XSS, etc.)
- ✅ Internal network (MySQL ve Redis dışarıya kapalı)

### ✅ Performans
- ✅ PHP 8.3 + JIT Compiler
- ✅ OPcache optimizasyonu
- ✅ Redis cache + session
- ✅ Nginx Gzip compression
- ✅ Static asset caching (1 yıl)
- ✅ PHP-FPM dynamic process management

### ✅ Yönetim
- ✅ Systemd service (boot'ta otomatik başlatma)
- ✅ SSL otomatik yenileme (cron)
- ✅ Health checks (MySQL, Redis, App)
- ✅ Log rotation
- ✅ Graceful shutdown

### ✅ Geliştirici Dostu
- ✅ Tek komutla deployment
- ✅ Tek komutla güncelleme (`./redeploy.sh`)
- ✅ Detaylı error logging
- ✅ Container durumu izleme

---

## 📊 Sistem Gereksinimleri

### Minimum:
- **CPU:** 1 core
- **RAM:** 2GB (swap ile)
- **Disk:** 20GB
- **OS:** Ubuntu 20.04+

### Önerilen:
- **CPU:** 2 cores
- **RAM:** 4GB
- **Disk:** 40GB
- **OS:** Ubuntu 22.04 LTS

---

## 🔄 Güncelleme İşlemi

Kodda değişiklik yaptın ve GitHub'a push ettin mi?

```bash
cd /var/www/aifilm
./redeploy.sh
```

Bu komut:
1. ✅ GitHub'dan son kodu çeker
2. ✅ Container'ları rebuild eder
3. ✅ Migration çalıştırır
4. ✅ Cache'leri temizler
5. ✅ Laravel'i optimize eder

**Downtime:** ~30-60 saniye

---

## 🔧 Yararlı Komutlar

```bash
# Container durumu
docker ps

# Logları izle
docker logs aifilm_app -f

# Laravel komutları
docker exec aifilm_app php artisan migrate
docker exec aifilm_app php artisan cache:clear

# Container restart
docker restart aifilm_app

# Tüm sistemi durdur/başlat
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d
```

---

## 🗄️ Veritabanı Yedekleme

```bash
# Otomatik yedek (deployment bilgilerini kullanarak)
DB_NAME=$(grep "^DB Name:" ~/deployment-info-*.txt | awk '{print $3}')
DB_ROOT_PASS=$(grep "^DB Root Password:" ~/deployment-info-*.txt | awk '{print $4}')
docker exec aifilm_mysql mysqldump -u root -p"${DB_ROOT_PASS}" ${DB_NAME} > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Crontab ile otomatik yedek (her gün 02:00):**
```bash
crontab -e

# Ekle:
0 2 * * * cd /var/www/aifilm && docker exec aifilm_mysql mysqldump -u root -p"$(grep 'DB Root Password:' ~/deployment-info-*.txt | awk '{print $4}')" $(grep 'DB Name:' ~/deployment-info-*.txt | awk '{print $3}') > ~/backups/aifilm_$(date +\%Y\%m\%d).sql
```

---

## 🐛 Sorun Giderme

### Site açılmıyor?
```bash
docker ps                           # Container'lar çalışıyor mu?
docker logs aifilm_app --tail=50   # Hata var mı?
docker restart aifilm_app           # Restart dene
```

### Cache sorunları?
```bash
docker exec aifilm_app php artisan optimize:clear
docker exec aifilm_app php artisan optimize
docker restart aifilm_app
```

### SSL hatası?
```bash
sudo certbot certificates          # Sertifika kontrolü
docker restart aifilm_nginx        # Nginx restart
```

### Tüm sistemi sıfırla (DİKKAT: Veriler silinir!)
```bash
cd /var/www/aifilm
docker compose -f docker-compose.prod.yml down -v
./deploy.sh
```

---

## 📞 Destek

**Detaylı kılavuz:** `INSTALL.md` dosyasını okuyun

**GitHub Issues:** https://github.com/mevlut0334/aifilm/issues

**Log dosyaları:**
- Laravel: `docker exec aifilm_app tail -100 /var/www/html/storage/logs/laravel.log`
- Nginx: `docker logs aifilm_nginx`
- MySQL: `docker logs aifilm_mysql`
- Redis: `docker logs aifilm_redis`

---

## ⚠️ Önemli Notlar

1. **Domain DNS:** Deployment'tan önce domain'i sunucuya yönlendirmeyi unutma!
2. **Firewall:** Port 80 ve 443 açık olmalı
3. **Deployment Bilgileri:** `~/deployment-info-*.txt` dosyasını güvenli bir yere yedekle
4. **Şifreler:** Script tarafından üretilen random şifreler deployment dosyasında kayıtlı
5. **SSL Yenileme:** Otomatik (her gece 03:00'te kontrol edilir)

---

## 🎉 Başarılı Deployment Sonrası

```bash
✅ Site: https://aifilm.com
✅ SSL: Aktif ve otomatik yenileme ayarlı
✅ Database: MySQL 8.0 + Redis
✅ Cache: Redis ile hızlandırılmış
✅ Logs: Docker logs ile erişilebilir
✅ Backup: Deployment bilgileri ~/deployment-info-aifilm.com.txt
✅ Boot: Systemd service ile otomatik başlatma
```

**Test et:**
- Ana sayfa: https://aifilm.com
- Health check: Container'ların durumunu kontrol et

**İzle:**
```bash
docker ps
docker stats
```

---

**Kolay gelsin! 🚀**

*Bu deployment sistemi production-ready ve battle-tested'dir. Herhangi bir sorunla karşılaşırsan INSTALL.md dosyasındaki detaylı troubleshooting bölümüne bak.*
