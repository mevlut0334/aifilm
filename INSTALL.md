# AiFilm - Kurulum Talimatları

## 📋 Son Güncellemeler (Nisan 2026)

- ✅ **Otomatik Kurulum** - Sadece 2 soru (Domain + Email)
- ✅ **Redis Cache** - Yüksek performans için Redis entegrasyonu
- ✅ **Akıllı DB İsimlendirme** - Domain'den otomatik DB adı
- ✅ **RAM Kontrolü** - Düşük RAM'de otomatik swap oluşturma
- ✅ **Sistem Nginx Yönetimi** - Port çakışmasını önler
- ✅ **SSL Otomasyonu** - Certbot standalone + cron job
- ✅ **Bilgi Saklama** - Deployment bilgileri dosyaya kaydedilir
- ✅ **OPcache + JIT** - PHP 8.3 JIT compiler ile süper hız
- ✅ **Auto Restart** - Systemd service ile boot'ta otomatik başlatma

---

## 🚀 Yeni Sunucuya Kurulum Adımları

### 1️⃣ Sunucuya SSH ile Bağlan
```bash
ssh ubuntu@SUNUCU_IP
```

### 2️⃣ Sistem Güncellemesi
```bash
sudo apt update && sudo apt upgrade -y
```

### 3️⃣ Docker Kurulumu
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt-get install docker-compose-plugin -y
sudo usermod -aG docker $USER
newgrp docker
```

### 4️⃣ Firewall Ayarları
```bash
sudo apt install ufw -y
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
# MySQL ve Redis portunu AÇMA - Docker internal network kullanacak
sudo ufw --force enable
```

### 5️⃣ **Tek Komutla Otomatik Kurulum! 🎉**

**Önemli:** Domain DNS kayıtlarını önce sunucuya yönlendir!

```bash
curl -fsSL https://raw.githubusercontent.com/mevlut0334/aifilm/main/deploy.sh | bash
```

veya manuel kurulum (daha stabil):
```bash
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www

cd /var/www
git clone https://github.com/mevlut0334/aifilm.git aifilm
cd aifilm
chmod +x deploy.sh
./deploy.sh
```

**Script Sadece 2 Soru Sorar:**
- **Domain adı:** `aifilm.com`
- **SSL Email:** `admin@aifilm.com`

Script otomatik olarak:
- ✅ Domain'den DB adı oluşturur (`aifilm` -> `aifilm_db`)
- ✅ Güvenli random şifreler üretir (DB, Redis)
- ✅ RAM kontrolü yapar, gerekirse swap ekler
- ✅ Sistem Nginx'i durdurur (port çakışması önlenir)
- ✅ SSL sertifikası alır (certbot standalone)
- ✅ Redis'i şifre ile yapılandırır
- ✅ Container'ları başlatır
- ✅ Migration + Seed çalıştırır
- ✅ Laravel'i optimize eder (OPcache + JIT)
- ✅ SSL otomatik yenileme için cron job ekler
- ✅ Systemd service oluşturur (boot'ta otomatik başlatma)
- ✅ Tüm bilgileri `~/deployment-info-aifilm.com.txt` dosyasına kaydeder

---

## ✅ Kurulum Sonrası Kontroller

### 📊 Container'ları Kontrol Et
```bash
cd /var/www/aifilm
docker ps
```

**Beklenen Çıktı:** 
```
aifilm_nginx     Up
aifilm_app       Up (healthy)
aifilm_mysql     Up (healthy)
aifilm_redis     Up (healthy)
```

### 📝 Deployment Bilgilerini Görüntüle
```bash
cat ~/deployment-info-aifilm.com.txt
```

Bu dosyada:
- Database bilgileri (name, user, password, root password)
- Redis password
- APP_KEY
- Domain ve SSL bilgileri

### 📋 Log'ları İzle
```bash
docker logs aifilm_app -f
docker logs aifilm_nginx -f
docker logs aifilm_mysql -f
docker logs aifilm_redis -f
```

### 🌐 Siteyi Test Et
1. Ana sayfa: `https://aifilm.com`
2. Admin panel: `https://aifilm.com/admin/login` (varsa)

---

## 🔧 Yararlı Yönetim Komutları

### Container Yönetimi

```bash
# Tüm container'ları göster
docker ps

# Container'ları durdur
cd /var/www/aifilm
docker compose -f docker-compose.prod.yml down

# Container'ları başlat
docker compose -f docker-compose.prod.yml up -d

# Belirli bir container'ı restart et
docker restart aifilm_nginx
docker restart aifilm_app
docker restart aifilm_mysql
docker restart aifilm_redis
```

### GitHub'dan Güncelleme (Redeploy)

```bash
# Proje dizinindeyken
cd /var/www/aifilm
./redeploy.sh

# veya herhangi bir yerden
cd /var/www/aifilm && ./redeploy.sh
```

**Redeploy scripti otomatik olarak:**
- ✅ GitHub'dan son kodu çeker
- ✅ Container'ları yeniden build eder
- ✅ Migration çalıştırır
- ✅ Cache'i temizler ve optimize eder

### Laravel Komutları

```bash
# Cache temizle
docker exec aifilm_app php artisan cache:clear
docker exec aifilm_app php artisan config:clear
docker exec aifilm_app php artisan view:clear
docker exec aifilm_app php artisan route:clear

# Optimize et
docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan route:cache
docker exec aifilm_app php artisan view:cache
docker exec aifilm_app php artisan optimize

# Migration
docker exec aifilm_app php artisan migrate --force

# Veritabanını sıfırla (⚠️ Tüm veriler silinir!)
docker exec aifilm_app php artisan migrate:fresh --seed --force

# Storage link
docker exec aifilm_app php artisan storage:link

# Queue worker (eğer kullanıyorsanız)
docker exec -d aifilm_app php artisan queue:work --tries=3
```

### Veritabanı Yönetimi

```bash
# Veritabanı yedeği al
DB_NAME=$(grep "^DB Name:" ~/deployment-info-*.txt | awk '{print $3}')
DB_ROOT_PASS=$(grep "^DB Root Password:" ~/deployment-info-*.txt | awk '{print $4}')
docker exec aifilm_mysql mysqldump -u root -p"${DB_ROOT_PASS}" ${DB_NAME} > backup_$(date +%Y%m%d_%H%M%S).sql

# Yedekten geri yükle
docker exec -i aifilm_mysql mysql -u root -p"${DB_ROOT_PASS}" ${DB_NAME} < backup_20260424_120000.sql

# MySQL içine gir
docker exec -it aifilm_mysql mysql -u root -p

# Redis içine gir
REDIS_PASS=$(grep "^Redis Password:" ~/deployment-info-*.txt | awk '{print $3}')
docker exec -it aifilm_redis redis-cli -a "${REDIS_PASS}"
```

### SSL Yönetimi

```bash
# SSL sertifikasını manuel yenile
sudo certbot renew

# SSL sertifikası durumunu kontrol et
sudo certbot certificates

# SSL yenileme cron job'ını kontrol et
crontab -l | grep certbot
```

---

## 🐛 Sorun Giderme

### ❌ Site Açılmıyor (500 Error veya Timeout)

```bash
# 1. Container'lar çalışıyor mu?
docker ps

# 2. Laravel log'larını kontrol et
docker exec aifilm_app tail -100 /var/www/html/storage/logs/laravel.log

# 3. Nginx log'larını kontrol et
docker logs aifilm_nginx --tail=50

# 4. .env dosyası var mı?
docker exec aifilm_app cat /var/www/html/.env | head -10

# 5. Container'ları yeniden başlat
docker restart aifilm_app aifilm_nginx
```

### 🖼️ Görseller Yüklenmiyor

```bash
# Storage link'i kontrol et
docker exec aifilm_app ls -la /var/www/html/public/storage

# Yoksa yeniden oluştur
docker exec aifilm_app php artisan storage:link

# İzinleri düzelt
docker exec aifilm_app chown -R www-data:www-data /var/www/html/storage
docker exec aifilm_app chown -R www-data:www-data /var/www/html/public
docker exec aifilm_app chmod -R 775 /var/www/html/storage
```

### 🔑 APP_KEY Hatası

```bash
# APP_KEY yeniden oluştur
docker exec aifilm_app php artisan key:generate --force
docker exec aifilm_app php artisan config:clear
docker restart aifilm_app
```

### 🗄️ MySQL Bağlantı Hatası

```bash
# MySQL çalışıyor mu?
docker logs aifilm_mysql --tail=30

# MySQL'i yeniden başlat
docker restart aifilm_mysql

# 10 saniye bekle, sonra test et
sleep 10
docker exec aifilm_app php artisan migrate:status
```

### 📦 Redis Bağlantı Hatası

```bash
# Redis çalışıyor mu?
docker logs aifilm_redis --tail=30

# Redis'i test et
REDIS_PASS=$(grep "^Redis Password:" ~/deployment-info-*.txt | awk '{print $3}')
docker exec aifilm_redis redis-cli -a "${REDIS_PASS}" ping

# Redis'i yeniden başlat
docker restart aifilm_redis
```

### 🐳 Container Başlamıyor

```bash
# Log'ları kontrol et
docker logs aifilm_app
docker logs aifilm_nginx
docker logs aifilm_mysql
docker logs aifilm_redis

# Container'ları tamamen sil ve yeniden başlat
cd /var/www/aifilm
docker compose -f docker-compose.prod.yml down -v
docker compose -f docker-compose.prod.yml up -d --build
```

### 🔒 SSL Hatası

```bash
# Certbot lock dosyalarını temizle
sudo pkill certbot
sudo rm -rf /tmp/certbot-*

# Sertifikayı manuel al
sudo certbot certonly --standalone --non-interactive --agree-tos --email admin@aifilm.com -d aifilm.com --http-01-port=80

# Nginx'i restart et
docker restart aifilm_nginx
```

### 🔄 Sunucu Restart Sonrası

```bash
# Container'lar otomatik başladı mı kontrol et
docker ps

# Başlamadıysa systemd service kontrol et
sudo systemctl status aifilm

# Manuel başlat
sudo systemctl start aifilm

# Auto-restart policy kontrol et
docker inspect aifilm_app | grep -A 3 RestartPolicy
```

### 💾 Düşük Disk Alanı

```bash
# Docker temizliği
docker system prune -a --volumes -f

# Eski image'ları sil
docker image prune -a -f

# Disk kullanımını kontrol et
df -h
docker system df
```

### 🧠 Yüksek RAM Kullanımı

```bash
# Container kaynak kullanımını göster
docker stats

# PHP-FPM worker sayısını azalt
# docker/php/php-fpm.conf dosyasında:
# pm.max_children = 3 (varsayılan 5)
# Dosyayı düzenle ve redeploy yap
```

---

## 🚨 Önemli Notlar

### 📂 Deployment Bilgileri Dosyası

Script çalıştıktan sonra:
```bash
~/deployment-info-aifilm.com.txt
```

Bu dosyada **hassas bilgiler** var:
- ✅ Dosya izinleri: `600` (sadece siz okuyabilir)
- ⚠️ Bu dosyayı **GÜVENLİ** bir yere yedekleyin
- ⚠️ Dosyayı **asla** public repository'ye eklemeyin

### 🔐 .env Dosyası Güvenliği

- `.env` dosyası Docker volume olarak mount edilir
- Container restart edilse bile kaybolmaz ✅
- Sunucu restart edilse bile kalır ✅
- `/var/www/aifilm/.env` konumundadır

### ⚡ PHP-FPM Optimizasyonları

Orta seviye sunucular için optimize edilmiş:
- **pm.max_children = 5** (varsayılan)
- **memory_limit = 512M**
- **OPcache + JIT enabled** (PHP 8.3 performance boost)

Eğer sunucunuzda 8GB+ RAM varsa:
```bash
# docker/php/php-fpm.conf dosyasını düzenle
pm.max_children = 10
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5

# docker/php/php.ini dosyasını düzenle
memory_limit = 1024M

# Container'ı rebuild et
cd /var/www/aifilm
docker compose -f docker-compose.prod.yml up -d --build
```

### 🔥 Firewall ve Güvenlik

**MySQL ve Redis Portları:**
- Port `3306` (MySQL) firewall'da **AÇILMAMALI** ❌
- Port `6379` (Redis) firewall'da **AÇILMAMALI** ❌
- Docker internal network kullanır ✅
- Dışarıdan erişim gerekirse SSH tunnel kullanın

**SSH Tunnel Örneği:**
```bash
# Local makinenizden
ssh -L 3307:localhost:3306 -L 6380:localhost:6379 ubuntu@SUNUCU_IP
# Artık localhost:3307'den MySQL, localhost:6380'den Redis'e erişebilirsiniz
```

### 🔄 Auto Restart Policy

Tüm container'lar `restart: unless-stopped` ile çalışır:
- ✅ Sunucu reboot edilirse container'lar otomatik başlar
- ✅ Container crash olursa otomatik restart olur
- ⚠️ `docker stop` ile durdurursanız, manuel `docker start` gerekir

### 📅 SSL Otomatik Yenileme

Script otomatik olarak cron job ekler:
```bash
# Her gece saat 03:00'te SSL yenileme kontrolü
0 3 * * * certbot renew --quiet --deploy-hook 'docker restart aifilm_nginx'
```

Kontrol et:
```bash
crontab -l
```

---

## 🔐 Güvenlik Checklist

Kurulumdan sonra yapılması gerekenler:

- [ ] Admin şifresini değiştir (varsa)
- [ ] `~/deployment-info-*.txt` dosyasını güvenli yere yedekle
- [ ] SSH şifresi yerine SSH key kullan
- [ ] Firewall ayarlarını kontrol et (`sudo ufw status`)
- [ ] SSL sertifikası yenileme cron job'ını kontrol et (`crontab -l`)
- [ ] Düzenli veritabanı yedeği al (haftada 1-2 kez)
- [ ] `fail2ban` kur (brute force saldırılarına karşı)
- [ ] Log dosyalarını düzenli kontrol et

**Fail2ban Kurulumu (Opsiyonel):**
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📞 Destek ve İletişim

Sorun yaşarsan kontrol et:

1. ✅ Container'lar çalışıyor mu? → `docker ps`
2. ✅ Log'larda hata var mı? → `docker logs aifilm_app`
3. ✅ .env dosyası var mı? → `docker exec aifilm_app cat /var/www/html/.env | head -10`
4. ✅ Storage izinleri doğru mu? → `docker exec aifilm_app ls -la /var/www/html/storage`
5. ✅ Redis bağlantısı çalışıyor mu? → `docker logs aifilm_redis`
6. ✅ SSL sertifikası geçerli mi? → `sudo certbot certificates`
7. ✅ Disk alanı yeterli mi? → `df -h`
8. ✅ RAM kullanımı normal mi? → `free -h`

**GitHub Issues:**
https://github.com/mevlut0334/aifilm/issues

---

## 📚 Ek Kaynaklar

- [Laravel Resmi Dokümantasyonu](https://laravel.com/docs)
- [Docker Compose Dokümantasyonu](https://docs.docker.com/compose/)
- [Let's Encrypt Dokümantasyonu](https://letsencrypt.org/docs/)
- [Nginx Dokümantasyonu](https://nginx.org/en/docs/)
- [Redis Dokümantasyonu](https://redis.io/docs/)

---

**Son Güncelleme:** 24 Nisan 2026
**Script Adı:** `deploy.sh` (v1.0)
**Kullanım:** Her sunucuya bir domain
**Minimum Sunucu Gereksinimleri:**
- RAM: 2GB (4GB+ önerilir)
- Disk: 20GB
- OS: Ubuntu 20.04 / 22.04 / 24.04
- Docker: 20.10+
- Docker Compose: 2.0+

---

## 🎯 Güncelleme Notları

### Redeploy İşlemi

Projede değişiklik yapıp GitHub'a push ettikten sonra:

```bash
cd /var/www/aifilm
./redeploy.sh
```

Bu script:
1. GitHub'dan son kodu çeker
2. Container'ları rebuild eder
3. Migration çalıştırır
4. Cache'leri temizler
5. Laravel'i optimize eder

**Önemli:** Redeploy sırasında site 30-60 saniye erişilemez olacaktır.
