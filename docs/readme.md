# AI Generation Platform

## Proje Özeti

Bu proje 3 ana katmandan oluşan production-grade bir Laravel sistemidir:

1. Çok dilli public web sitesi
2. Özel admin paneli
3. Mobil uygulamalar için JSON API

Kullanıcılar:

* hazır görsel template
* hazır video template
* custom görsel talepleri (yatay / dikey / kare seçeneğiyle)
* custom video talepleri

oluşturabilir.

Admin panel üzerinden:

* token yönetimi
* template yönetimi (yatay / dikey / kare şablon yükleme desteğiyle)
* request yönetimi
* blog yönetimi
* SEO yönetimi
* output link teslimi

sağlanır.

---

## Tech Stack

* Laravel 13
* PHP 8.3
* MySQL 8
* Redis
* Bootstrap 5
* Blade
* Alpine.js
* TipTap
* Laravel Sanctum
* Docker + Docker Compose
* Paddle (web ödemeleri)
* mcamara/laravel-localization
* spatie/laravel-translatable
* spatie/laravel-sitemap

---

## Dokümantasyon

| Dosya | İçerik |
|---|---|
| `phases.md` | Geliştirme fazları ve aktif faz |
| `architecture.md` | Katmanlar, klasör yapısı, trait sistemi |
| `features.md` | Feature listesi ve detayları |
| `rules.md` | Kod standartları ve AI davranış kuralları |
| `data.md` | Tablolar ve veri kuralları |
| `api.md` | Endpoint listesi ve response standardı |

---

## Local Development

```bash
docker-compose up -d
php artisan migrate
php artisan db:seed
```

Local URL:

```
http://127.0.0.1:8080
```
