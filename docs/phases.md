# Phases

Proje 8 fazda geliştirilir. Her faz tamamlanmadan bir sonrakine geçilmez.

Her fazda ilgili modülün Web + Admin + API katmanları birlikte yapılır.

faz durumları  todo / in-progress / done

---

## Faz 1 — Altyapı & Auth

**Durum:** `done`

* Docker + Docker Compose kurulumu
* Veritabanı ve migration altyapısı
* Çoklu dil altyapısı (`en` default, `tr`)
* Kayıt / giriş / çıkış
* Sanctum token auth
* Profil görüntüleme ve güncelleme
* Admin girişi (ayrı guard)
* Base layout'lar: Web, Admin, API

---

## Faz 2 — Tema & Arayüz Temeli

**Durum:** `done`

* Light / dark mode (localStorage bazlı tema tercihi)
* Merkezi CSS değişken sistemi (Bootstrap 5 native variables)
* Bootstrap 5 theme token entegrasyonu (`data-bs-theme` attribute)
* Web ve Admin ortak UI bileşenleri (navbar, theme toggle button)
* Auto mode (sistem temasını takip etme)

---

## Faz 3 — Token & Ödeme

**Durum:** `done` 

* Token tablosu ve hareketleri
* Başlangıç token yükleme
* Paddle entegrasyonu (web)
* Apple In-App Purchase doğrulama (iOS)
* Google Play Billing doğrulama (Android)
* Satın alma sonrası token yükleme (tüm platformlar)
* Admin token yönetimi (manuel yükleme / düşme)

---

## Faz 4 — Template Sistemi

**Durum:** `done`

* Template oluşturma / düzenleme / silme (Admin)
* Her template için landscape / portrait / square asset yükleme (Admin)
* Template listeleme ve detay (Web + API)
* Orientation bazlı filtreleme
* Token maliyeti tanımlama (Admin)

---

## Faz 5 — Custom Request & Teslim

**Durum:** `todo`

* Custom görsel talebi oluşturma (orientation seçimi ile)
* Custom video talebi oluşturma
* Template bazlı talep oluşturma
* Token düşümü
* Admin talep yönetimi (listeleme, detay, durum güncelleme)
* Admin output link teslimi
* Kullanıcı talep geçmişi ve output görüntüleme

---

## Faz 6 — Blog Sistemi

**Durum:** `todo`

* Kategori ve etiket yönetimi (Admin)
* Blog yazısı oluşturma / düzenleme / silme (Admin)
* TipTap editor entegrasyonu
* Locale bazlı içerik ve yayın durumu
* Web blog listeleme ve detay sayfası
* API blog endpointleri

---

## Faz 7 — SEO

**Durum:** `todo`

* Locale bazlı meta alanları
* hreflang (`en`, `tr`, `x-default` → `en`)
* Canonical URL yönetimi
* Sitemap (`spatie/laravel-sitemap`)
* Admin SEO sayfa yönetimi
