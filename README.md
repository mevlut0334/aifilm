# 🎬 AiFilm

AI-powered film analysis and recommendation platform built with Laravel 11.

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 🚀 Production Deployment

**Quick Start (Ubuntu Server):**

```bash
# 1. Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt-get install docker-compose-plugin -y
sudo usermod -aG docker $USER
newgrp docker

# 2. Clone & Deploy
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
cd /var/www
git clone https://github.com/mevlut0334/aifilm.git aifilm
cd aifilm
chmod +x deploy.sh
./deploy.sh
```

**That's it!** Script will ask for:
1. Domain name (e.g., `aifilm.com`)
2. SSL email (e.g., `admin@aifilm.com`)

Everything else is automatic! ✨

**Features:**
- ✅ SSL/TLS with Let's Encrypt (auto-renewal)
- ✅ Nginx + PHP-FPM + MySQL + Redis
- ✅ Production optimized (OPcache + JIT)
- ✅ Auto-restart on boot (systemd)
- ✅ Security headers & firewall ready
- ✅ One-command updates (`./redeploy.sh`)

**📚 Documentation:**
- [**DEPLOYMENT.md**](DEPLOYMENT.md) - Quick deployment guide
- [**INSTALL.md**](INSTALL.md) - Detailed installation & troubleshooting
- [**REDEPLOY-GUIDE.md**](REDEPLOY-GUIDE.md) - Update & redeploy guide

---

## 🔄 Deployment Scripts - Hangi Scripti Ne Zaman Kullanmalı?

### 🆕 İlk Kurulum: `deploy.sh`

**Ne Zaman?**
- Yeni bir sunucuya **ilk kez** kurulum yapıyorsan
- SSL sertifikası almak istiyorsan

**Ne Sorar?**
1. Domain adı (örn: `aifilm.com`)
2. SSL email (örn: `admin@aifilm.com`)

**Kullanımı:**
```bash
cd /var/www/aifilm
chmod +x deploy.sh
./deploy.sh
```

### 🔄 Normal Güncelleme: `redeploy.sh`

**Ne Zaman?**
- GitHub'a yeni kod push ettin
- Sunucuda **hiç değişiklik yapmadın**
- Normal güncellemeler için (en çok bu kullanılır)

**Kullanımı:**
```bash
cd /var/www/aifilm
./redeploy.sh
```

**Downtime:** ~15-30 saniye

### ⚠️ Zorla Güncelleme: `force-redeploy.sh`

**Ne Zaman?**
- Sunucuda test için değişiklik yaptın ve geri almak istiyorsun
- Git conflict hatası alıyorsun
- Container'larda sorun var

**Kullanımı:**
```bash
cd /var/www/aifilm
./force-redeploy.sh  # Onay isteyecek
```

**⚠️ Dikkat:** Local değişiklikleri siler!

**Downtime:** ~45-60 saniye

---

## 📋 Günlük İş Akışı

1. **Local'de kod yaz** (Windows/Mac bilgisayarında)
2. **GitHub'a push et:**
   ```bash
   git add .
   git commit -m "Yeni özellik eklendi"
   git push origin main
   ```
3. **Sunucuya bağlan ve güncelle:**
   ```bash
   ssh ubuntu@sunucu_ip
   cd /var/www/aifilm
   ./redeploy.sh
   ```
4. **Site açılışını kontrol et** → `https://aifilm.com`

---

## 💻 Local Development

```bash
# 1. Clone repository
git clone https://github.com/mevlut0334/aifilm.git
cd aifilm

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Start Docker containers
docker compose up -d

# 5. Run migrations
docker exec aifilm_app php artisan migrate --seed

# 6. Build assets
npm run dev
```

**Access:**
- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081

**Note:** Local development kullanır `docker-compose.yml`, Production kullanır `docker-compose.prod.yml`

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11 + PHP 8.3
- **Frontend:** Blade + Vite + Tailwind CSS
- **Database:** MySQL 8.0
- **Cache:** Redis
- **Server:** Nginx + PHP-FPM
- **SSL:** Let's Encrypt
- **Container:** Docker + Docker Compose

---

## 📦 Features

- AI-powered film analysis
- Personalized recommendations
- User reviews and ratings
- Advanced search and filters
- Admin dashboard
- RESTful API

---

## 📊 Deployment Scripts Karşılaştırması

| Durum | Kullanılacak Script | Açıklama |
|-------|---------------------|----------|
| 🆕 Sunucuya ilk kez kuruyorum | `./deploy.sh` | SSL + DB + her şey kurulur |
| 📤 GitHub'a kod push ettim | `./redeploy.sh` | Normal güncelleme (en çok bu) |
| 🧪 Sunucuda test değişikliği yaptım | `./force-redeploy.sh` | Local değişiklikleri sil |
| ⚠️ Git conflict hatası | `./force-redeploy.sh` | Zorla GitHub'dan çek |
| 🐳 Container'lar çalışmıyor | `./force-redeploy.sh` | Sıfırdan başlat |

---

## 📁 Proje Dosya Yapısı

```
aifilm/
├── deploy.sh                    # İlk kurulum scripti
├── redeploy.sh                  # Normal güncelleme scripti
├── force-redeploy.sh            # Zorla güncelleme scripti
├── docker-compose.yml           # Local development config
├── docker-compose.prod.yml      # Production config
├── Dockerfile                   # Local development Dockerfile
├── Dockerfile.prod              # Production optimized Dockerfile
├── docker/
│   ├── nginx/
│   │   └── prod.conf.template   # Nginx SSL config
│   └── php/
│       ├── php.ini              # PHP config (OPcache + JIT)
│       └── php-fpm.conf         # PHP-FPM worker config
├── DEPLOYMENT.md                # Deployment kılavuzu
├── INSTALL.md                   # Detaylı kurulum kılavuzu
└── REDEPLOY-GUIDE.md            # Güncelleme kılavuzu
```

---

## ⚠️ Önemli Notlar

### Production Deployment:
- ✅ `deploy.sh` → **Sadece 1 kez** kullanılır (ilk kurulumda)
- ✅ `redeploy.sh` → **Her güncelleme için** kullanılır (en çok bu)
- ✅ `force-redeploy.sh` → **Acil durumlar için** kullanılır (dikkatli ol)

### Domain DNS Ayarları:
- ⚠️ Deployment'tan **önce** domain'i sunucuya yönlendir
- ⚠️ A kaydı: `aifilm.com` → `SUNUCU_IP`
- ⚠️ Kontrol: `nslookup aifilm.com` veya `dig aifilm.com +short`

### Güvenlik:
- ⚠️ `~/deployment-info-*.txt` dosyasını güvenli yere yedekle
- ⚠️ Bu dosyada DB şifreleri, Redis şifresi ve APP_KEY var
- ⚠️ Asla public repository'ye ekleme

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
