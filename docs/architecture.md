# Architecture

## Sistem Katmanları

### Web

```
/en/...   → İngilizce (default)
/tr/...   → Türkçe
```

* Default dil: `en`
* Desteklenen diller: `en`, `tr`
* Yeni dil eklemek için `config/laravellocalization.php` dosyasına locale eklenmesi yeterlidir
* `mcamara/laravel-localization` paketi ile yönetilir
* Locale bilinmeyen URL'ler default dile yönlendirilir
* Blade
* Bootstrap 5
* Alpine.js

### Admin

```
/admin
```

* sadece Türkçe
* ayrı layout
* ayrı controller yapısı

### API

```
/api/v1/
```

* JSON only
* Sanctum auth

---

## Katmanlı Mimari

```
Controller
→ Form Request
→ Service
→ Repository
→ Model
```

### Yasaklar

* Controller içinde business logic yazılmaz
* Controller içinde query yazılmaz
* Fat controller kullanılmaz
* Fat model kullanılmaz

---

## Klasör Yapısı

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Web/
│   │   ├── Admin/
│   │   └── Api/
│   ├── Requests/
│   │   ├── Web/
│   │   ├── Admin/
│   │   └── Api/
├── Services/
├── Repositories/
├── Observers/
├── Jobs/
├── Policies/
├── DTOs/
├── Enums/
└── Traits/
```

---

## Trait Sistemi

Tekrar eden davranışlar Trait olarak `app/Traits/` altında tanımlanır ve ilgili Model, Service veya Repository sınıflarında `use` ile dahil edilir.

### Trait Kullanım Kuralları

* Trait yalnızca birden fazla sınıfta tekrar eden davranışı kapsüller
* Tek bir sınıfa özgü mantık Trait'e taşınmaz
* Trait içinde doğrudan query yazılmaz; Repository üzerinden çalışılır
* Trait isimleri amacı açıkça yansıtır: `HasUuid`, `HasSlug`, `HasOrientation` vb.
* Her Trait tek sorumluluk (SRP) ilkesine uyar

### Mevcut Trait'ler

```
app/Traits/
├── HasUuid.php              → Model'lerde otomatik UUID ataması
├── HasSlug.php              → Slug üretimi ve güncellenmesi
├── HasOrientation.php       → Görsel yönlendirme (landscape/portrait/square) yönetimi
├── HasSeoFields.php         → SEO meta alanlarını ortak sunan modeller için
├── HasTokenCost.php         → Token düşümü hesaplama mantığı
├── HasLocaleScope.php       → Locale bazlı query scope'ları
└── InteractsWithStorage.php → Dosya yükleme ve silme işlemleri
```

### HasUuid

```php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
```

### HasOrientation

```php
namespace App\Traits;

trait HasOrientation
{
    public function isLandscape(): bool
    {
        return $this->orientation === 'landscape';
    }

    public function isPortrait(): bool
    {
        return $this->orientation === 'portrait';
    }

    public function isSquare(): bool
    {
        return $this->orientation === 'square';
    }

    public function getAspectRatioAttribute(): string
    {
        return match($this->orientation) {
            'landscape' => '16:9',
            'portrait'  => '9:16',
            'square'    => '1:1',
            default     => '1:1',
        };
    }

    public function scopeByOrientation($query, string $orientation)
    {
        return $query->where('orientation', $orientation);
    }
}
```