# Rules

## Bu Projeye Kod Yazarken Uyulacak Davranış Kuralları

> Bu bölüm AI için birincil rehberdir. Kod yazmadan önce mutlaka okunmalıdır.

* Kod yazmadan önce `phases.md`, `architecture.md`, `rules.md`, `data.md` dosyalarını oku
* `phases.md`'deki aktif fazın dışına çıkma; o fazla ilgili olmayan kod yazma
* Var olan bir pattern varsa onu taklit et, yeni pattern icat etme
* Şüphe duyduğunda tahmin etme, sor
* Migration yazmadan önce `data.md`'deki tablo listesini kontrol et
* Yeni bir davranış eklemeden önce aynı davranışın başka bir sınıfta olup olmadığını kontrol et
* Bir özellik zaten varsa yeniden yazma, genişlet
* Dosya oluştururken hangi katmana ait olduğuna karar ver: `Web`, `Admin`, `Api`
* Her katmanın controller'ı sadece kendi katmanına ait iş yapar

---

## Kod Standartları

* Laravel best practices zorunlu
* PSR standardı uygulanır
* SOLID prensipleri uygulanır
* DRY prensibi korunur
* KISS tercih edilir

---

## Mimari Kurallar

* Controller içinde business logic yazılmaz
* Controller içinde query yazılmaz
* Validation controller içine yazılmaz, Form Request kullanılır
* Service layer zorunludur; her işlem bir Service üzerinden geçer
* Repository pattern zorunludur; Eloquent query'leri doğrudan controller veya service içine yazılmaz
* Eager loading zorunludur
* N+1 sıfır tolerans
* Fat controller kullanılmaz
* Fat model kullanılmaz

---

## Trait Kuralları

* Tekrar eden davranışlar mutlaka Trait olarak tanımlanmalı
* Trait içinde doğrudan Eloquent query yazılmaz
* Trait tek sorumluluk (SRP) ilkesine uyar
* Yeni Trait eklemeden önce `architecture.md`'deki mevcut Trait listesini kontrol et

---

## Katman Kuralları

* API controller asla View döndürmez
* Web ve Admin controller JSON döndürmez
* Admin panel translate edilmez, sadece Türkçe'dir
* Livewire kullanılmaz
* Tailwind kullanılmaz

---

## Veri Kuralları

* Token ve fiyat alanları integer tutulur; float veya decimal kullanılmaz
* `OrientationEnum` dışında orientation string'i hardcode yazılmaz
* Locale bazlı içerikler JSON translation olarak saklanır

---

## Frontend Kuralları

* Renkler merkezi CSS değişkenleri ile yönetilir; hardcode CSS renk değeri kullanımı yasaktır
* Tema sadece light/dark moddan oluşur; admin panelden tema yönetimi yapılmaz
* Bootstrap 5 CSS değişkenleri ve theme token'ları kullanılır