
# 🚀 AiFilm - Production Deployment Kılavuzu

AiFilm, Laravel tabanlı uygulamanın Docker Compose ile production ortamında çalıştırılması için hazırlanmıştır.

Bu doküman mevcut production sunucu mimarisini ve güvenli güncelleme sürecini açıklar.

## 📌 Production Mimarisi

- **İşletim sistemi:** Ubuntu 24.04 LTS
- **Uygulama:** Laravel
- **PHP:** PHP 8.3
- **Web sunucusu:** Nginx
- **Uygulama çalışma ortamı:** PHP-FPM
- **Veritabanı:** MySQL
- **Cache ve kuyruk:** Redis
- **Container yönetimi:** Docker Compose
- **Reverse proxy ve SSL:** Merkezi Traefik
- **Kaynak kod:** GitHub

### Container yapısı

| Container | Görevi |
|---|---|
| `aifilm_app` | Laravel ve PHP-FPM |
| `aifilm_nginx` | Nginx web sunucusu |
| `aifilm_queue` | Laravel queue işlemleri |
| `aifilm_redis` | Cache ve queue altyapısı |
| `aifilm_mysql` | MySQL veritabanı |
| `traefik` | Merkezi reverse proxy ve SSL yönetimi |

Traefik, Docker üzerindeki uygulamalara merkezi olarak trafik yönlendirir. AiFilm kendi Nginx container'ı üzerinden uygulama trafiğini karşılar.

**Önemli:** Traefik ortak altyapıdır. AiFilm deployment işlemleri sırasında Traefik durdurulmamalı veya yeniden yapılandırılmamalıdır.

---

## 📁 Proje Konumu

Production sunucusundaki proje dizini:

```bash
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

`.env` ve `.env.docker` production ortamına ait ayarları içerir. Bu dosyalar GitHub'a gönderilmemelidir.

---

## ⚡ İlk Kurulum

Bu işlem yalnızca yeni bir production kurulumu içindir.

Mevcut çalışan production sunucusunda tekrar çalıştırılmamalıdır.

### 1. Sunucuya bağlan

```bash
ssh administrator@SUNUCU_IP
```

### 2. Docker kurulumunu kontrol et

```bash
docker --version
docker compose version
```

Docker kurulu değilse resmi Docker kurulum dokümanını kullan:

https://docs.docker.com/engine/install/ubuntu/

### 3. Projeyi klonla

```bash
sudo mkdir -p /var/www
sudo chown -R "$USER":"$USER" /var/www

cd /var/www

git clone https://github.com/mevlut0334/aifilm.git
cd aifilm
```

### 4. İlk kurulum scriptini çalıştır

```bash
chmod +x deploy.sh
./deploy.sh
```

Scriptin istediği bilgileri girerek kurulumu tamamla.

> `deploy.sh` ilk kurulum içindir. Mevcut veritabanı bulunan production sisteminde tekrar çalıştırma.

---

## 🔄 Normal Güncelleme

Geliştirme bilgisayarında yaptığın değişiklikleri GitHub'a gönderdikten sonra production sunucusunda şu adımları uygula.

### 1. Sunucuya bağlan

```bash
ssh administrator@SUNUCU_IP
```

### 2. Proje dizinine geç

```bash
cd /var/www/aifilm
```

### 3. GitHub'dan güncel kodu çek

```bash
git fetch origin main
git pull --ff-only origin main
```

Bu işlem sunucudaki kodu GitHub'daki `main` dalıyla günceller.

### 4. Normal deployment işlemini başlat

```bash
chmod +x redeploy.sh
./redeploy.sh
```

`redeploy.sh` genel olarak şu işlemleri gerçekleştirir:

1. GitHub'daki güncel kodu alır.
2. Docker Compose yapılandırmasını kontrol eder.
3. Gerekli container'ları build eder ve başlatır.
4. Laravel storage ve cache izinlerini düzenler.
5. Veritabanı migration işlemlerini çalıştırır.
6. Laravel cache işlemlerini gerçekleştirir.
7. Queue worker'ların güncel kodla çalışmasını sağlar.
8. PHP-FPM container'ını yeniden başlatarak OPcache'in eski kodu kullanmasını önler.
9. Türkçe ve İngilizce sayfaların HTTP yanıtlarını kontrol eder.

### 5. Deployment sonrası kontrol

```bash
docker ps
```

Uygulama logları:

```bash
docker logs aifilm_app --tail=100
```

Web sunucusu logları:

```bash
docker logs aifilm_nginx --tail=100
```

Site kontrolleri:

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

Her iki adresin de başarılı HTTP yanıtı vermesi beklenir.

---

## ⚠️ Normal Deployment ve Force Deployment Farkı

### Normal deployment

```bash
./redeploy.sh
```

Günlük kod güncellemelerinde kullanılmalıdır.

GitHub'daki değişiklikleri sunucuya alır ve normal deployment işlemlerini yürütür.

### Force deployment

```bash
./force-redeploy.sh
```

Yalnızca normal deployment ile çözülemeyen özel durumlarda kullanılmalıdır.

Bu script:

- GitHub'daki `origin/main` sürümünü esas alır.
- Sunucudaki takip edilen dosyalarda bulunan yerel değişiklikleri silebilir.
- Container'ları yeniden oluşturabilir.

**Dikkat:** `git reset --hard origin/main` yerel kod değişikliklerini geri döndürülemez şekilde kaybettirebilir. Çalıştırmadan önce sunucuda saklanması gereken bir değişiklik olmadığını kontrol et.

Force deployment veritabanını sıfırlamak için kullanılmaz. Ancak container'ların yeniden oluşturulması sırasında kısa süreli kesinti yaşanabilir.

---

## 🧠 PHP OPcache ve Güncelleme

Production ortamında PHP OPcache etkin olarak çalışır.

PHP ayarlarında aşağıdaki değerler bulunabilir:

```ini
opcache.enable=1
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

`opcache.validate_timestamps=0` olduğunda PHP-FPM, PHP dosyalarındaki değişiklikleri otomatik olarak kontrol etmez.

Bu nedenle yalnızca GitHub'dan yeni kodu çekmek yeterli olmayabilir. PHP-FPM eski kodu bellekte tutmaya devam edebilir.

AiFilm deployment sürecinde bu nedenle `aifilm_app` container'ı yeniden başlatılır.

**Önemli:** CLI üzerinden çalıştırılan `opcache_reset()` komutu PHP-FPM'in ayrı OPcache belleğini sıfırlamaz. Bu yüzden deployment sonrasında PHP-FPM container'ının yeniden başlatılması gerekir.

---

## 🛠️ Yararlı Docker Komutları

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

### Queue worker'ları yenileme

```bash
docker exec aifilm_app php artisan queue:restart
```

### Uygulama container'ını yeniden başlatma

```bash
docker restart aifilm_app
```

---

## 🗄️ Veritabanı Yedekleme

Production veritabanı önemli kullanıcı ve uygulama verilerini içerir.

Deployment öncesinde özellikle veritabanı değişikliği içeren işlemlerde yedek alınması önerilir.

MySQL container'ının erişim bilgileri `.env.docker` dosyasındaki mevcut yapılandırmaya göre kullanılmalıdır.

Örnek yedekleme komutu:

```bash
cd /var/www/aifilm

mkdir -p backups

docker exec aifilm_mysql sh -c \
  'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  > "backups/aifilm_$(date +%Y%m%d_%H%M%S).sql"
```

Yedek dosyasının oluştuğunu kontrol et:

```bash
ls -lh backups/
```

> Bu örnek komut, MySQL container ortam değişkenlerinin `MYSQL_USER`, `MYSQL_PASSWORD` ve `MYSQL_DATABASE` adlarıyla tanımlı olmasını varsayar. Mevcut Compose yapılandırmasındaki değişken adları farklıysa komut buna göre uyarlanmalıdır.

Yedekleri yalnızca aynı sunucuda tutmak yerine güvenli başka bir konuma da kopyalamak önerilir.

---

## 🔐 Güvenlik ve Veri Koruma

- `.env` ve `.env.docker` dosyalarını GitHub'a gönderme.
- Production veritabanı bilgilerini paylaşma.
- MySQL ve Redis servislerini doğrudan internete açma.
- Traefik ortak altyapısını AiFilm deployment işlemlerinden bağımsız tut.
- Veritabanı yedeklerini düzenli al.
- Migration işlemlerini production ortamında dikkatli uygula.
- `docker compose down -v` komutunu kullanma.

### Önemli veri koruma kuralı

Aşağıdaki komut production ortamında kullanılmamalıdır:

```bash
docker compose -f docker-compose.prod.yml down -v
```

`-v` parametresi Compose tarafından yönetilen volume'ları silebilir. Bu durum MySQL verilerinin kaybolmasına neden olabilir.

Sorun giderme sırasında veritabanı ve Redis volume'larını silmek yerine ilgili container ve logları incele.

---

## 🐛 Sorun Giderme

### Site açılmıyorsa

```bash
docker ps
docker logs aifilm_app --tail=100
docker logs aifilm_nginx --tail=100
```

### Laravel hata veriyorsa

```bash
docker exec aifilm_app tail -100 /var/www/html/storage/logs/laravel.log
```

### Cache sorunları varsa

```bash
docker exec aifilm_app php artisan optimize:clear
docker exec aifilm_app php artisan config:cache
docker exec aifilm_app php artisan event:cache
docker exec aifilm_app php artisan view:cache
docker restart aifilm_app
```

### Sayfalar 404 dönüyorsa

Önce route kayıtlarını kontrol et:

```bash
docker exec aifilm_app php artisan route:list
```

Ardından PHP-FPM container'ını yeniden başlat:

```bash
docker restart aifilm_app
```

HTTP yanıtlarını kontrol et:

```bash
curl -I https://asilov.com/tr
curl -I https://asilov.com/en
```

### SSL veya yönlendirme sorunu varsa

Traefik loglarını kontrol et:

```bash
docker logs traefik --tail=100
```

Nginx loglarını kontrol et:

```bash
docker logs aifilm_nginx --tail=100
```

Traefik ortak altyapı olduğundan, yalnızca AiFilm'de sorun görülüyorsa öncelikle AiFilm container'larını incele.

---

## 📋 Deployment Öncesi Kontrol Listesi

- [ ] Değişiklikler yerel ortamda test edildi.
- [ ] GitHub `main` dalına gönderildi.
- [ ] Production `.env` dosyaları korunuyor.
- [ ] Veritabanı değişikliği varsa yedek alındı.
- [ ] Migration dosyaları kontrol edildi.
- [ ] Normal deployment scripti kullanılacak.
- [ ] Traefik ve ortak altyapı dosyalarına gereksiz müdahale edilmeyecek.
- [ ] Deployment sonrası `/tr` ve `/en` kontrol edilecek.

---

## 🌐 Production Adresleri

- Ana site: https://asilov.com
- Türkçe: https://asilov.com/tr
- İngilizce: https://asilov.com/en
- GitHub: https://github.com/mevlut0334/aifilm

---

## 📌 Temel Kurallar

1. İlk kurulum için `deploy.sh` kullanılır.
2. Normal güncellemeler için `redeploy.sh` kullanılır.
3. `force-redeploy.sh` yalnızca gerektiğinde kullanılır.
4. Production `.env` dosyaları korunur.
5. Veritabanı volume'ları silinmez.
6. Traefik ortak altyapısına gereksiz müdahale edilmez.
7. PHP kodu güncellendiğinde PHP-FPM'in yeniden başlatılması sağlanır.
8. Deployment sonrasında Türkçe ve İngilizce sayfalar kontrol edilir.

**AiFilm production güncellemelerinde temel yaklaşım: mevcut çalışan sistemi koruyarak yalnızca gerekli uygulama güncellemelerini gerçekleştirmektir.**
