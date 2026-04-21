# Data

## Ana Tablolar

| Tablo | Açıklama |
|---|---|
| `admins` | Admin kullanıcıları |
| `users` | Uygulama kullanıcıları |
| `user_theme_preferences` | Kullanıcının light/dark tercihi |
| `tokens` | Kullanıcı token hareketleri |
| `settings` | Sistem ayarları |
| `templates` | Görsel ve video şablonları |
| `template_assets` | Şablona ait dosyalar (orientation bazlı) |
| `generation_requests` | Kullanıcıların oluşturduğu talepler |
| `request_outputs` | Tamamlanan taleplerin çıktıları |
| `blog_posts` | Blog yazıları |
| `blog_categories` | Blog kategorileri |
| `blog_tags` | Blog etiketleri |
| `seo_pages` | Sayfa bazlı SEO alanları |

---

## Orientation Alanları

Aşağıdaki tablolarda `orientation` kolonu bulunur:

| Tablo | Tip | Değerler | Zorunlu |
|---|---|---|---|
| `templates` | ENUM | `landscape`, `portrait`, `square` | Evet |
| `template_assets` | ENUM | `landscape`, `portrait`, `square` | Evet |
| `generation_requests` | ENUM | `landscape`, `portrait`, `square` | Evet |

Migration örneği:

```php
$table->enum('orientation', ['landscape', 'portrait', 'square'])->default('square');
$table->index('orientation');
```

---

## Veri Kuralları

* Token integer tutulur
* Fiyat integer tutulur
* Locale bazlı içerikler JSON translation olarak saklanır (`spatie/laravel-translatable`)
* Her tablo index stratejisi ile optimize edilir
* EXPLAIN kontrolü zorunludur
* Orientation alanı ENUM tipinde saklanır; string validation Form Request içinde yapılır