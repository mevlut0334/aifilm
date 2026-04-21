# Features

## Ödeme Sistemi

* Web: Paddle
* iOS: Apple In-App Purchase
* Android: Google Play Billing
* Her platform kendi ödeme altyapısını kullanır; backend satın alma doğrulamasını alır ve token yükler

---

## Kullanıcı Sistemi

* kayıt / giriş
* Sanctum token auth
* başlangıç token yükleme
* profil yönetimi

---

## Template Sistemi

* image template (yatay / dikey / kare şablon desteği)
* video template
* asset upload
* token bazlı kullanım
* her şablon için `orientation` alanı zorunludur

---

## Custom Request Sistemi

* özel görsel üretimi
  * kullanıcı yatay (landscape), dikey (portrait) veya kare (square) seçeneğini belirler
  * seçilen orientation token maliyetini etkileyebilir (admin konfigürasyonu)
* özel video üretimi
* admin manuel teslim

---

## Görsel Yönlendirme (Orientation) Sistemi

### Kullanıcı Akışı

Custom görsel talebi oluştururken kullanıcıya üç seçenek sunulur:

| Seçenek | Enum Değeri | Örnek Boyut |
|---------|-------------|-------------|
| Yatay   | `landscape` | 1920×1080   |
| Dikey   | `portrait`  | 1080×1920   |
| Kare    | `square`    | 1080×1080   |

* Seçim API üzerinden `orientation` alanı ile iletilir
* Seçim `generation_requests` tablosunda saklanır
* Admin, talebi işlerken orientation bilgisini görür ve buna uygun çıktı üretir

### Admin Şablon Yükleme

Admin, template oluştururken her orientation için ayrı asset yükleyebilir:

* `template_assets` tablosunda her asset'in `orientation` alanı vardır
* Bir template'in landscape, portrait ve square asset'leri bağımsız yönetilir
* Kullanıcı bir template seçtiğinde kendi seçtiği orientation'a ait asset otomatik eşleşir
* Eşleşen orientation asset'i yoksa fallback olarak `square` kullanılır

### Teknik Akış

```
HasOrientation (Trait)
→ Template, GenerationRequest, TemplateAsset modellerinde kullanılır
→ Enums/OrientationEnum: landscape | portrait | square
→ GenerationRequestService orientation'ı doğrular ve asset eşleşmesini yapar
```

---

## Recreate Sistemi

* yeniden üretim
* segment bazlı recreate
* output replace
* tekrar token düşümü
* recreate sırasında orientation değiştirilebilir

---

## Blog Sistemi

* çok dilli blog
* locale bazlı status
* TipTap editor
* SEO alanları

---

## Tema ve Renk Yönetim Sistemi

* Bootstrap 5 token yapısı üzerinden çalışır
* Tailwind CSS kesinlikle kullanılmaz
* Tüm renkler merkezi CSS değişkenleri ile yönetilir
* Hardcode inline color kullanımı yasaktır
* Admin panelden tema yönetimi yoktur

### Kullanıcı Tema Seçimi

* light mode
* dark mode

Tema seçimi kullanıcı profiline kaydedilir ve oturumlar arasında korunur.

### Teknik Akış

```
CSS değişkenleri → Bootstrap theme tokens
Tema tercihi    → user_theme_preferences tablosunda saklanır
```

---

## SEO Sistemi

* Desteklenen locale'ler: `en` (default), `tr`
* locale bazlı meta alanları
* hreflang (`en`, `tr`, `x-default` → `en`)
* canonical
* sitemap
* x-default