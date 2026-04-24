# 🔄 AiFilm - Redeploy Kılavuzu

## 📋 3 Farklı Güncelleme Yöntemi

### 1. 🟢 Normal Güncelleme: `redeploy.sh`

**Ne zaman kullanılır?**
- GitHub'a push ettiğiniz kodları canlıya almak için
- Normal güncellemeler için
- Local değişiklik yapmadıysanız

**Özellikleri:**
- ✅ `git pull` kullanır (güvenli)
- ✅ Local değişiklikler varsa uyarı verir
- ✅ Container'ları yeniden build eder
- ✅ Migration çalıştırır
- ✅ Cache'leri temizler
- ✅ OPcache'i resetler
- ✅ **Downtime:** ~15-30 saniye

**Kullanım:**
```bash
cd /var/www/aifilm
./redeploy.sh
```

---

### 2. 🟡 Zorla Güncelleme: `force-redeploy.sh`

**Ne zaman kullanılır?**
- Local değişiklikler yaptınız ve silmek istiyorsunuz
- Git conflict var ve düzeltmek istemiyorsunuz
- Container'larda sorun var, sıfırdan başlatmak istiyorsunuz
- Database hariç her şeyi temizlemek istiyorsanız

**Özellikleri:**
- ⚠️ `git reset --hard` kullanır (local değişiklikleri siler!)
- ⚠️ `docker down -v` ile volume'ları temizler (Database hariç)
- ✅ Container'ları sıfırdan oluşturur
- ✅ Storage izinlerini düzeltir
- ✅ Migration çalıştırır
- ✅ **Downtime:** ~45-60 saniye

**Kullanım:**
```bash
cd /var/www/aifilm
./force-redeploy.sh
```

Script çalıştırınca onay isteyecek:
```
⚠️  Bu işlem:
  - Local tüm değişiklikleri silecek
  - GitHub'dan temiz kod çekecek
  - Container'ları tamamen yeniden oluşturacak

Devam edilsin mi? (y/n):
```

---

### 3. 🔴 İlk Kurulum: `deploy.sh`

**Ne zaman kullanılır?**
- Yeni bir sunucuya ilk kez kurulum yapıyorsanız
- SSL sertifikası almak istiyorsanız
- Systemd service oluşturmak istiyorsanız

**Kullanım:**
```bash
cd /var/www/aifilm
./deploy.sh
```

---

## 🎯 Hangi Scripti Kullanmalıyım?

### Durumunuza Göre Seçim:

| Durum | Kullanılacak Script | Açıklama |
|-------|---------------------|----------|
| GitHub'a kod push ettim | `./redeploy.sh` | Normal güncelleme |
| Sunucuda test değişikliği yaptım, geri almak istiyorum | `./force-redeploy.sh` | Local değişiklikleri sil |
| Container'lar çalışmıyor | `./force-redeploy.sh` | Sıfırdan başlat |
| Git conflict hatası alıyorum | `./force-redeploy.sh` | Temiz çek |
| İlk kez kuruyorum | `./deploy.sh` | Tam kurulum |
| SSL sertifikası yenilemek istiyorum | `sudo certbot renew` | SSL yenile |

---

## 📝 Detaylı Karşılaştırma

### `redeploy.sh` (Normal)

```bash
✅ Yapılanlar:
- git pull origin main
- Container'ları rebuild et
- Migration çalıştır
- Cache'leri optimize et
- OPcache resetle

⚠️ Yapmadıkları:
- Local değişiklikleri silmez
- Volume'ları temizlemez
- SSL almaz
- Systemd service oluşturmaz
```

### `force-redeploy.sh` (Zorla)

```bash
✅ Yapılanlar:
- git reset --hard origin/main (local değişiklikleri sil)
- git clean -fd (untracked dosyaları sil)
- docker down -v (volume'ları temizle)
- Container'ları sıfırdan oluştur
- Storage izinlerini düzelt
- Migration çalıştır
- Cache'leri optimize et

⚠️ Yapmadıkları:
- Database verilerini silmez (mysql_data volume korunur)
- SSL almaz
- .env.docker dosyasını silmez
```

### `deploy.sh` (İlk Kurulum)

```bash
✅ Yapılanlar:
- RAM/Swap kontrolü
- SSL sertifikası al
- .env ve .env.docker oluştur
- Container'ları başlat
- Migration + Seed çalıştır
- Systemd service oluştur
- Cron job ekle (SSL otomatik yenileme)
- Deployment bilgilerini kaydet

⚠️ Yapmadıkları:
- Mevcut kurulumu güncellemez (ilk kez kullanılır)
```

---

## 🔧 Script İçinde Neler Var?

### Ortak Özellikler (Her Script'te)

```bash
set -e  # Hata olursa dur
cd /var/www/aifilm  # Proje dizinine git
```

### Kritik Parametre: `--env-file .env.docker`

**ÇOK ÖNEMLİ!** Docker Compose komutlarında mutlaka bu parametre olmalı:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.docker up -d
```

**Neden?**
- `docker-compose.prod.yml` dosyası `${DOMAIN}`, `${DB_PASSWORD}` gibi değişkenler kullanıyor
- Bu değişkenler `.env.docker` dosyasında tanımlı
- Eğer `--env-file` parametresi olmazsa:
  - ❌ Domain tanınmaz
  - ❌ Nginx SSL bulamaz
  - ❌ MySQL şifresi yanlış olur
  - ❌ Redis şifresi yanlış olur

---

## 🚀 Hızlı Komutlar

```bash
# Normal güncelleme
./redeploy.sh

# Zorla güncelleme (onay ister)
./force-redeploy.sh

# Container durumu
docker ps

# Logları izle
docker logs aifilm_app -f

# Container'ları manuel restart
docker restart aifilm_app
docker restart aifilm_nginx

# Cache temizle (container içinde)
docker exec aifilm_app php artisan optimize:clear
docker exec aifilm_app php artisan optimize
```

---

## 🐛 Sorun Giderme

### Redeploy sonrası 500 hatası?

```bash
# 1. Container loglarını kontrol et
docker logs aifilm_app --tail=50

# 2. Laravel loglarını kontrol et
docker exec aifilm_app tail -50 /var/www/html/storage/logs/laravel.log

# 3. İzinleri kontrol et
docker exec aifilm_app ls -la /var/www/html/storage

# 4. Cache temizle
docker exec aifilm_app php artisan optimize:clear
docker exec aifilm_app php artisan optimize
docker restart aifilm_app
```

### Git conflict hatası?

```bash
# Force redeploy yap (local değişiklikleri siler)
./force-redeploy.sh
```

### Container başlamıyor?

```bash
# Logları kontrol et
docker logs aifilm_app
docker logs aifilm_mysql
docker logs aifilm_redis

# Force redeploy yap
./force-redeploy.sh
```

### Database bağlantı hatası?

```bash
# .env.docker dosyasını kontrol et
cat .env.docker

# MySQL container çalışıyor mu?
docker ps | grep mysql

# MySQL loglarını kontrol et
docker logs aifilm_mysql --tail=30

# MySQL'i restart et
docker restart aifilm_mysql
sleep 10
docker restart aifilm_app
```

---

## ⚠️ Önemli Hatırlatmalar

1. **Redeploy öncesi:**
   - ✅ Deployment bilgilerini yedekleyin: `~/deployment-info-*.txt`
   - ✅ Database yedek alın (ihtiyaten)
   - ✅ Peak saatlerde yapmayın (downtime olur)

2. **Redeploy sonrası:**
   - ✅ Site açılışını kontrol edin
   - ✅ Container'ları kontrol edin: `docker ps`
   - ✅ Logları kontrol edin: `docker logs aifilm_app -f`

3. **Force redeploy:**
   - ⚠️ Local değişiklikleri siler!
   - ⚠️ Emin değilseniz önce yedek alın
   - ⚠️ Volume'lar temizlenir (DB hariç)

---

## 📊 Downtime Süreleri

| Script | Ortalama Downtime |
|--------|-------------------|
| `redeploy.sh` | 15-30 saniye |
| `force-redeploy.sh` | 45-60 saniye |
| `deploy.sh` | İlk kurulum (downtime olmaz) |

**Not:** Build süresi sunucu özelliklerine göre değişir.

---

## 🎓 Best Practices

1. **Development'ta test et:**
   ```bash
   # Local'de docker-compose.yml ile test et
   docker compose up -d
   # Test yap
   # GitHub'a push et
   # Sonra sunucuda redeploy yap
   ```

2. **Küçük güncellemeler yap:**
   - Tek seferde çok şey değiştirmeyin
   - Sorun olursa geri dönmek zor olur

3. **Düzenli yedek:**
   ```bash
   # Haftada 1 kez DB yedek al
   # Crontab ile otomatikleştir
   ```

4. **Monitoring:**
   ```bash
   # Container'ları izle
   docker stats
   
   # Disk kullanımı
   df -h
   
   # Log boyutları
   docker system df
   ```

---

## 📞 Destek

**Detaylı kılavuz:** `INSTALL.md` dosyasını okuyun

**Deployment kılavuzu:** `DEPLOYMENT.md` dosyasını okuyun

**GitHub Issues:** https://github.com/mevlut0334/aifilm/issues

---

**Kolay gelsin! 🚀**
