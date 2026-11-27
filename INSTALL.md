
# AiFilm - Kurulum ve Production Yönetim Kılavuzu

AiFilm, Laravel tabanlı uygulamanın Docker Compose ile production ortamında çalıştırılması için hazırlanmıştır.

Bu doküman ilk kurulum, sunucu yönetimi, güncelleme, yedekleme ve sorun giderme işlemlerini açıklar.

---

## 1. Production Mimarisi

AiFilm production ortamında aşağıdaki teknolojileri kullanır:

| Bileşen | Teknoloji |
|---|---|
| İşletim sistemi | Ubuntu 24.04 LTS |
| Backend | Laravel |
| PHP | PHP 8.3 |
| Web sunucusu | Nginx |
| PHP çalışma ortamı | PHP-FPM |
| Veritabanı | MySQL |
| Cache ve queue | Redis |
| Container yönetimi | Docker Compose |
| Reverse proxy | Traefik |
| SSL | Merkezi Traefik yönetimi |
| Kaynak kod | GitHub |

### Docker container yapısı

| Container | Görevi |
|---|---|
| `aifilm_app` | Laravel ve PHP-FPM |
| `aifilm_nginx` | Nginx web sunucusu |
| `aifilm_queue` | Laravel queue işlemleri |
| `aifilm_redis` | Cache ve queue altyapısı |
| `aifilm_mysql` | MySQL veritabanı |
| `traefik` | Merkezi reverse proxy ve SSL |

**Önemli:** Traefik ortak altyapıdır. AiFilm kurulumu ve güncellemeleri sırasında Traefik durdurulmamalı veya gereksiz yere yeniden yapılandırılmamalıdır.

SSL sertifikaları ve dış trafik yönlendirmesi merkezi Traefik altyapısı üzerinden yönetilir. Bu nedenle AiFilm için ayrıca Certbot kurulumu veya bağımsız SSL cron job oluşturulması gerekmez.

---

## 2. Sistem Gereksinimleri

### Minimum önerilen gereksinimler

- CPU: 2 çekirdek
- RAM: 4 GB
- Disk: 20 GB
- İşletim sistemi: Ubuntu 24.04 LTS
- Docker Engine
- Docker Compose Plugin

Daha yüksek trafik ve yoğun video işleme işlemleri için sunucu kaynakları ayrıca değerlendirilmelidir.

---

## 3. Yeni Sunucuya Kurulum

> Bu bölüm yalnızca yeni bir production sunucusu kurulurken uygulanmalıdır. Mevcut çalışan AiFilm sunucusunda ilk kurulum işlemini tekrar çalıştırmayın.

### 3.1. Sunucuya SSH ile bağlan

```bash
ssh administrator@SUNUCU_IP
```

### 3.2. Sistemi güncelle

```bash
sudo apt update
sudo apt upgrade -y
```

### 3.3. Docker kurulumunu kontrol et

```bash
docker --version
docker compose version
```

Docker kurulu değilse resmi Ubuntu kurulum dokümanını kullan:

https://docs.docker.com/engine/install/ubuntu/

Docker kurulumu sonrasında kullanıcı izinlerini kontrol et.

```bash
docker ps
```

Yetki hatası alırsan Docker kullanıcı grubunu yapılandır:

```bash
sudo usermod -aG docker "$USER"
```

Oturumu kapatıp yeniden aç.

### 3.4. Firewall ayarları

Sunucunun güvenlik duvarında gerekli portları kontrol et.

- SSH: 22
- HTTP: 80
- HTTPS: 443

MySQL ve Redis portlarını doğrudan internete açma.

UFW kullanılıyorsa örnek:

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw status
```

Firewall'ı etkinleştirmeden önce SSH erişiminin izinli olduğundan emin ol.

### 3.5. Domain DNS ayarları

Kurulumdan önce domain DNS kayıtlarının sunucuya yönlendirilmesi gerekir.

Örnek:

```text
asilov.com       -> SUNUCU_IP
www.asilov.com   -> SUNUCU_IP
```

DNS değişikliklerinin yayılmasını bekle.

### 3.6. Merkezi Traefik altyapısını kontrol et

AiFilm, merkezi Traefik reverse proxy altyapısını kullanır.

Sunucuda Traefik container'ının çalıştığını kontrol et:

```bash
docker ps
```

Proxy ağının mevcut olduğunu kontrol et:

```bash
docker network ls
```

Kurulumdan önce merkezi proxy altyapısının çalışır durumda olması gerekir.

### 3.7. Projeyi GitHub'dan indir

```bash
sudo mkdir -p /var/www
sudo chown -R "$USER":"$USER" /var/www

cd /var/www

git clone https://github.com/mevlut0334/aifilm.git
cd aifilm
```

### 3.8. İlk kurulum scriptini çalıştır

```bash
chmod +x deploy.sh
./deploy.sh
```

Kurulum scripti gerekli yapılandırma bilgilerini isteyerek production ortamını hazırlar.

Scriptin istediği bilgileri doğru gir.

Kurulum tamamlandığında container durumlarını kontrol et:

```bash
docker ps
```

**Önemli:** `deploy.sh` ilk kurulum içindir. Mevcut veritabanı bulunan production ortamında tekrar çalıştırılmamalıdır.

---

## 4. Production Dosyaları

Proje dizini:

```text
/var/www/aifilm
```

Önemli dosyalar:

```text
aifilm/
├── deploy.sh
├── redeploy.sh
├── force-redeploy.sh
├── docker-compose.prod.yml
├── Dockerfile.prod
├── .env
├── .env.docker
├── docker/
│   ├── nginx/
│   └── php/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
└── storage/
```

### Ortam dosyaları

`.env` ve `.env.docker` production ortamına ait özel yapılandırma bilgilerini içerir.

Bu dosyalar:

- GitHub'a gönderilmemelidir.
- Yetkisiz kişilerle paylaşılmamalıdır.
- Deployment sırasında korunmalıdır.
- Uygulama ve veritabanı bağlantı ayarları için kullanılmalıdır.

---

## 5. Kurulum Sonrası Kontroller

### Container durumları

```bash
docker ps
```

AiFilm servislerinin çalışır durumda olduğunu kontrol et:

- `aifilm_app`
- `aifilm_nginx`
- `aifilm_queue`
- `aifilm_redis`
- `aifilm_mysql`

### Laravel logları

```bash
docker exec aifilm_app tail -100 /var/www/html/storage/logs/laravel.log
```

### Nginx logları

```bash
docker logs aifilm_nginx --tail=100
```

### MySQL logları

```bash
docker logs aifilm_mysql --tail=100
```

### Redis logları

```bash
docker logs aifilm_redis --tail=100
```

### Site testi

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

Türkçe ve İngilizce sayfaların başarılı HTTP yanıtı vermesi beklenir.

---

## 6. GitHub'dan Güncelleme (Redeploy)

Geliştirme bilgisayarında yaptığın değişiklikleri GitHub `main` dalına gönderdikten sonra production sunucusunda güncelleme yapabilirsin.

### 6.1. Sunucuya bağlan

```bash
ssh administrator@SUNUCU_IP
```

### 6.2. Proje dizinine geç

```bash
cd /var/www/aifilm
```

### 6.3. GitHub'dan güncel kodu çek

```bash
git fetch origin main
git pull --ff-only origin main
```

### 6.4. Deployment scriptini çalıştır

```bash
chmod +x redeploy.sh
./redeploy.sh
```

### Normal redeploy işlemi

`redeploy.sh` aşağıdaki işlemleri gerçekleştirir:

1. GitHub'dan güncel kodu alır.
2. Docker Compose yapılandırmasını kontrol eder.
3. Gerekli container'ları build eder ve başlatır.
4. Storage ve cache izinlerini düzenler.
5. Laravel migration işlemlerini çalıştırır.
6. Laravel cache işlemlerini oluşturur.
7. Queue worker'ların güncel kodla çalışmasını sağlar.
8. PHP-FPM container'ını yeniden başlatarak eski OPcache kodunun kullanılmasını önler.
9. `/tr` ve `/en` sayfalarını kontrol eder.

### Güncelleme sonrası kontrol

```bash
docker ps
docker logs aifilm_app --tail=100
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

---

## 7. Normal Redeploy ve Force Redeploy

### Normal güncelleme

```bash
./redeploy.sh
```

Günlük güncellemelerde kullanılmalıdır.

### Force güncelleme

```bash
./force-redeploy.sh
```

Yalnızca normal deployment ile çözülemeyen özel durumlarda kullanılmalıdır.

Force deployment, GitHub'daki `origin/main` sürümünü esas alır ve sunucudaki takip edilen yerel dosya değişikliklerini silebilir.

**Dikkat:** Force deployment öncesinde sunucuda saklanması gereken yerel değişiklik bulunmadığından emin ol.

Force deployment veritabanını sıfırlamak için kullanılmaz. Container'ların yeniden oluşturulması sırasında kısa süreli erişim kesintisi yaşanabilir.

---

## 8. PHP OPcache ve PHP-FPM

Production ortamında PHP OPcache etkin olarak çalışır.

Örnek OPcache ayarları:

```ini
opcache.enable=1
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

`opcache.validate_timestamps=0` olduğunda PHP-FPM, PHP dosyalarındaki değişiklikleri otomatik olarak kontrol etmez.

Bu nedenle yalnızca GitHub'dan kodu çekmek her zaman yeterli değildir. PHP-FPM belleğinde eski kod bulunabilir.

AiFilm deployment sürecinde `aifilm_app` container'ı yeniden başlatılarak PHP-FPM'in yeni kodla çalışması sağlanır.

**Önemli:** CLI üzerinden çalıştırılan `opcache_reset()` komutu PHP-FPM'in ayrı OPcache belleğini sıfırlamaz. Bu nedenle container restart işlemi önemlidir.

---

## 9. Yararlı Docker Komutları

### Container durumları

```bash
docker ps
```

### Kaynak kullanımı

```bash
docker stats
```

### Uygulama logları

```bash
docker logs aifilm_app -f
```

### Nginx logları

```bash
docker logs aifilm_nginx -f
```

### Container yeniden başlatma

```bash
docker restart aifilm_app
```

### Laravel migration

```bash
docker exec aifilm_app php artisan migrate --force
```

### Laravel cache temizleme

```bash
docker exec aifilm_app php artisan optimize:clear
```

### Laravel cache oluşturma

```bash
docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan event:cache
docker exec aifilm_app php artisan view:cache
```

### Queue worker yenileme

```bash
docker exec aifilm_app php artisan queue:restart
```

> Queue worker'lar ayrı `aifilm_queue` container'ında çalıştığı için queue container loglarını da kontrol et.

```bash
docker logs aifilm_queue --tail=100
```

---

## 10. Veritabanı Yedekleme

Production veritabanı kullanıcı ve uygulama verilerini içerir.

Önemli migration veya veritabanı değişikliklerinden önce yedek alınması önerilir.

Örnek yedekleme işlemi:

```bash
cd /var/www/aifilm

mkdir -p backups

docker exec aifilm_mysql sh -c \
  'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  > "backups/aifilm_$(date +%Y%m%d_%H%M%S).sql"
```

Bu örnek komut MySQL container ortam değişkenlerinin `MYSQL_USER`, `MYSQL_PASSWORD` ve `MYSQL_DATABASE` olarak tanımlandığını varsayar. Mevcut Compose yapılandırmasındaki değişken adları farklıysa komut uyarlanmalıdır.

Yedekleri kontrol et:

```bash
ls -lh backups/
```

Yedeklerin yalnızca aynı sunucuda tutulması önerilmez. Güvenli başka bir depolama alanına da kopyalanmalıdır.

---

## 11. Sorun Giderme

### Site açılmıyorsa

```bash
docker ps
docker logs aifilm_app --tail=100
docker logs aifilm_nginx --tail=100
```

### Laravel 500 hatası veriyorsa

```bash
docker exec aifilm_app tail -100 /var/www/html/storage/logs/laravel.log
```

### Sayfalar 404 dönüyorsa

Route kayıtlarını kontrol et:

```bash
docker exec aifilm_app php artisan route:list
```

PHP-FPM container'ını yeniden başlat:

```bash
docker restart aifilm_app
```

Sayfaları tekrar kontrol et:

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

### Görseller yüklenmiyorsa

Storage link'ini kontrol et:

```bash
docker exec aifilm_app ls -la /var/www/html/public/storage
```

Gerekirse oluştur:

```bash
docker exec aifilm_app php artisan storage:link
```

Storage izinlerini kontrol et:

```bash
docker exec aifilm_app ls -la /var/www/html/storage
```

### MySQL bağlantı sorunu varsa

```bash
docker logs aifilm_mysql --tail=100
docker exec aifilm_app php artisan migrate:status
```

### Redis bağlantı sorunu varsa

```bash
docker logs aifilm_redis --tail=100
docker logs aifilm_queue --tail=100
```

### Traefik veya SSL sorunu varsa

```bash
docker logs traefik --tail=100
docker logs aifilm_nginx --tail=100
```

Traefik ortak altyapı olduğundan, yalnızca AiFilm'de sorun görülüyorsa öncelikle AiFilm container'larını incele.

### Sunucu yeniden başlatıldıysa

```bash
docker ps
```

Container'ların yeniden başladığını kontrol et. Başlamayan servislerin loglarını incele.

---

## 12. Güvenlik ve Veri Koruma

### Production ortamında dikkat edilmesi gerekenler

- `.env` ve `.env.docker` dosyalarını GitHub'a gönderme.
- Veritabanı ve Redis portlarını doğrudan internete açma.
- Traefik ortak altyapısını gereksiz yere değiştirme.
- Production veritabanını düzenli yedekle.
- Migration işlemlerini kontrollü gerçekleştir.
- APP_KEY değerini mevcut sistemde sebepsiz yere değiştirme.
- Force deployment öncesinde yerel değişiklikleri kontrol et.
- Veritabanı volume'larını silme.

### Production ortamında kullanılmaması gereken komutlar

Aşağıdaki komut veritabanı verilerinin silinmesine neden olabilir:

```bash
docker compose -f docker-compose.prod.yml down -v
```

Aşağıdaki komut da Docker tarafından yönetilen volume'ları silebilir:

```bash
docker system prune -a --volumes -f
```

Veritabanını sıfırlayabilecek Laravel komutları da production ortamında kullanılmamalıdır:

```bash
php artisan migrate:fresh
```

**Önemli:** Bu komutları normal sorun giderme veya deployment işlemlerinde kullanma.

---

## 13. Production Kontrol Listesi

### İlk kurulum öncesi

- [ ] Ubuntu sunucu hazır.
- [ ] Docker ve Docker Compose kurulu.
- [ ] Domain DNS kayıtları doğru.
- [ ] Merkezi Traefik çalışıyor.
- [ ] Proxy Docker ağı mevcut.
- [ ] Production ortam dosyaları hazır.

### Güncelleme öncesi

- [ ] Değişiklikler yerel ortamda test edildi.
- [ ] GitHub `main` dalına gönderildi.
- [ ] Production `.env` dosyaları korunuyor.
- [ ] Veritabanı değişikliği varsa yedek alındı.
- [ ] Normal deployment scripti kullanılacak.

### Güncelleme sonrası

- [ ] Container'lar çalışıyor.
- [ ] Laravel loglarında kritik hata yok.
- [ ] Queue container çalışıyor.
- [ ] MySQL bağlantısı çalışıyor.
- [ ] Redis bağlantısı çalışıyor.
- [ ] `/tr` sayfası açılıyor.
- [ ] `/en` sayfası açılıyor.
- [ ] SSL ve domain yönlendirmesi çalışıyor.

---

## 14. Production Adresleri

- Ana site: https://asilov.com
- Türkçe: https://asilov.com/tr
- İngilizce: https://asilov.com/en
- GitHub: https://github.com/mevlut0334/aifilm

---

## 15. Temel Deployment Kuralları

1. `deploy.sh` yalnızca ilk kurulum içindir.
2. `redeploy.sh` normal güncellemeler için kullanılır.
3. `force-redeploy.sh` yalnızca gerektiğinde kullanılır.
4. Production `.env` dosyaları korunur.
5. Veritabanı volume'ları silinmez.
6. Traefik ortak altyapısına gereksiz müdahale edilmez.
7. PHP kodu güncellendiğinde PHP-FPM yeniden başlatılır.
8. Deployment sonrasında `/tr` ve `/en` sayfaları kontrol edilir.
9. Veritabanı değişikliklerinden önce yedek alınır.

**AiFilm production yaklaşımı:** Mevcut çalışan sistemi koruyarak yalnızca gerekli uygulama güncellemelerini gerçekleştirmek.
