# API Documentation

## Base URL

```
/api/v1/
```

## Authentication

Laravel Sanctum Bearer Token sistemi kullanılır.

```http
Authorization: Bearer {token}
```

## Language Header

Her istekte dil belirtmek için `Accept-Language` header'ı kullanılır.

```http
Accept-Language: en
```

Desteklenen diller: `en`, `tr`

---

## Response Standardı

Tüm API yanıtları aşağıdaki formatta döner:

```json
{
  "success": true,
  "message": "İşlem başarılı",
  "data": {},
  "locale": "en"
}
```

Hata durumunda:

```json
{
  "success": false,
  "message": "Hata mesajı",
  "data": null,
  "locale": "en"
}
```

---

# Endpoint'ler

## 1. Authentication & Profil İşlemleri

### 1.1 Kayıt Ol (Register)

Yeni kullanıcı kaydı oluşturur.

```
POST /api/v1/register
```

**Request Body:**

```json
{
  "first_name": "Ahmet",
  "last_name": "Yılmaz",
  "email": "ahmet@example.com",
  "password": "12345678",
  "password_confirmation": "12345678",
  "country_code": "+90",
  "phone": "5551234567"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "first_name": "Ahmet",
      "last_name": "Yılmaz",
      "email": "ahmet@example.com",
      "country_code": "+90",
      "phone": "5551234567",
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

### 1.2 Giriş Yap (Login)

Kullanıcı girişi yapar ve Bearer token döner.

```
POST /api/v1/login
```

**Request Body:**

```json
{
  "email": "ahmet@example.com",
  "password": "12345678"
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
  },
  "locale": "tr"
}
```

Bu token'ı sonraki isteklerde `Authorization: Bearer {token}` olarak kullanın.

---

### 1.3 Çıkış Yap (Logout)

Kullanıcı oturumunu sonlandırır ve token'ı geçersiz kılar.

```
POST /api/v1/logout
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Logged out",
  "data": null,
  "locale": "tr"
}
```

---

### 1.4 Profil Bilgileri (Profile)

Giriş yapmış kullanıcının profil bilgilerini getirir.

```
GET /api/v1/profile
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": null,
  "data": {
    "user": {
      "id": 1,
      "first_name": "Ahmet",
      "last_name": "Yılmaz",
      "email": "ahmet@example.com",
      "country_code": "+90",
      "phone": "5551234567",
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

## 2. Template Bazlı Görsel/Video Talepleri

### 2.1 Template Listesi (Templates)

Aktif template'leri listeler. Orientation'a göre filtreleme yapılabilir.

```
GET /api/v1/templates
GET /api/v1/templates?orientation=landscape
```

**Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

- `orientation` (optional): `landscape`, `portrait`, `square`

**Response (200):**

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "title": {
        "en": "Sunset City",
        "tr": "Gün Batımı Şehri"
      },
      "description": {
        "en": "Beautiful sunset over futuristic city",
        "tr": "Fütüristik şehir üzerinde güzel gün batımı"
      },
      "token_cost": 50,
      "landscape_video_url": "https://example.com/storage/templates/landscape.mp4",
      "portrait_video_url": "https://example.com/storage/templates/portrait.mp4",
      "square_video_url": "https://example.com/storage/templates/square.mp4",
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  ],
  "locale": "tr"
}
```

---

### 2.2 Template Detay

Belirli bir template'in detaylarını getirir.

```
GET /api/v1/templates/{uuid}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": null,
  "data": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "title": {
      "en": "Sunset City",
      "tr": "Gün Batımı Şehri"
    },
    "description": {
      "en": "Beautiful sunset over futuristic city",
      "tr": "Fütüristik şehir üzerinde güzel gün batımı"
    },
    "token_cost": 50,
    "landscape_video_url": "https://example.com/storage/templates/landscape.mp4",
    "portrait_video_url": "https://example.com/storage/templates/portrait.mp4",
    "square_video_url": "https://example.com/storage/templates/square.mp4",
    "created_at": "2026-04-24T12:00:00+00:00"
  },
  "locale": "tr"
}
```

---

### 2.3 Template ile Talep Oluşturma

Template kullanarak görsel veya video üretim talebi oluşturur. Template bazlı taleplerde kullanıcının bir görsel yüklemesi zorunludur.

```
POST /api/v1/generation-requests
```

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Template Görsel) - Form Data:**

```json
{
  "type": "template_image",
  "template_id": "550e8400-e29b-41d4-a716-446655440000",
  "orientation": "landscape",
  "input_image": "[FILE]"
}
```

**Request Body (Template Video) - Form Data:**

```json
{
  "type": "template_video",
  "template_id": "550e8400-e29b-41d4-a716-446655440000",
  "orientation": "portrait",
  "input_image": "[FILE]"
}
```

**Not:** Template bazlı taleplerde `input_image` parametresi **zorunludur**. Yüklenen görsel, template ile birlikte işlenir.

**Response (201):**

```json
{
  "success": true,
  "message": "Request created successfully",
  "data": {
    "request": {
      "uuid": "660e8400-e29b-41d4-a716-446655440001",
      "type": "template_image",
      "status": "pending",
      "progress": 0,
      "token_cost": 50,
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

### 2.4 Generasyon Talep Listesi

Kullanıcının oluşturduğu tüm template bazlı talepleri listeler.

```
GET /api/v1/generation-requests
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Requests retrieved successfully",
  "data": {
    "requests": [
      {
        "uuid": "660e8400-e29b-41d4-a716-446655440001",
        "type": "template_image",
        "status": "completed",
        "progress": 100,
        "orientation": "landscape",
        "description": null,
        "token_cost": 50,
        "input_image_url": null,
        "output_url": "https://example.com/storage/outputs/image.jpg",
        "failure_reason": null,
        "template": {
          "uuid": "550e8400-e29b-41d4-a716-446655440000",
          "title": "Sunset City",
          "token_cost": 50
        },
        "created_at": "2026-04-24T12:00:00+00:00",
        "updated_at": "2026-04-24T12:05:00+00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    }
  },
  "locale": "tr"
}
```

---

### 2.5 Generasyon Talep Detayı

Belirli bir talebin detaylarını getirir.

```
GET /api/v1/generation-requests/{uuid}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Request retrieved successfully",
  "data": {
    "request": {
      "uuid": "660e8400-e29b-41d4-a716-446655440001",
      "type": "template_image",
      "status": "completed",
      "progress": 100,
      "orientation": "landscape",
      "description": null,
      "token_cost": 50,
      "input_image_url": null,
      "output_url": "https://example.com/storage/outputs/image.jpg",
      "failure_reason": null,
      "template": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "title": "Sunset City",
        "token_cost": 50
      },
      "created_at": "2026-04-24T12:00:00+00:00",
      "updated_at": "2026-04-24T12:05:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

### 2.6 Generasyon Talebi İptal Etme

Bekleyen veya işleniyor durumundaki talebi iptal eder.

```
DELETE /api/v1/generation-requests/{uuid}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Request cancelled successfully",
  "data": null,
  "locale": "tr"
}
```

---

## 3. Custom Görsel Talepleri

### 3.1 Custom Görsel Talebi Oluşturma

Kullanıcı kendi açıklamasıyla özel görsel üretim talebi oluşturur.

```
POST /api/v1/generation-requests
```

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```json
{
  "type": "custom_image",
  "orientation": "landscape",
  "description": "Fütüristik şehir manzarası, gün batımı ışığı, mor-turuncu gökyüzü",
  "input_image": "[FILE]"
}
```

**Not:** `input_image` opsiyoneldir. Gönderilirse reference olarak kullanılır.

**Response (201):**

```json
{
  "success": true,
  "message": "Request created successfully",
  "data": {
    "request": {
      "uuid": "770e8400-e29b-41d4-a716-446655440002",
      "type": "custom_image",
      "status": "pending",
      "progress": 0,
      "token_cost": 100,
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

### 3.2 Custom Görsel Talepleri Listesi

Kullanıcının custom görsel taleplerini template bazlı taleplerle birlikte görür (aynı endpoint).

```
GET /api/v1/generation-requests
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response:** Bölüm 2.4'teki ile aynı formattadır. `type` alanı `custom_image` olan kayıtlar custom görsellerdir.

---

## 4. Custom Video Talepleri & Segment Düzenleme

### 4.1 Custom Video Talebi Oluşturma

Kullanıcı prompt ile özel video üretim talebi oluşturur.

```
POST /api/v1/custom-video-requests
```

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```json
{
  "prompt": "Uzayda seyahat eden bir uzay gemisi, gezegenler arası yolculuk",
  "input_image": "[FILE]"
}
```

**Not:** `input_image` opsiyoneldir. Gönderilirse ilk frame olarak kullanılır.

**Response (201):**

```json
{
  "success": true,
  "message": "Request created successfully",
  "data": {
    "request": {
      "uuid": "880e8400-e29b-41d4-a716-446655440003",
      "prompt": "Uzayda seyahat eden bir uzay gemisi, gezegenler arası yolculuk",
      "input_image_path": "https://example.com/storage/custom-video-requests/input.jpg",
      "status": "pending",
      "token_cost": 200,
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

---

### 4.2 Custom Video Talepleri Listesi

Kullanıcının oluşturduğu tüm custom video taleplerini listeler.

```
GET /api/v1/custom-video-requests
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Requests retrieved successfully",
  "data": {
    "requests": [
      {
        "uuid": "880e8400-e29b-41d4-a716-446655440003",
        "prompt": "Uzayda seyahat eden bir uzay gemisi",
        "input_image_path": "https://example.com/storage/custom-video-requests/input.jpg",
        "status": "processing",
        "token_cost": 200,
        "overall_progress": 35,
        "segments_count": 4,
        "completed_segments": 1,
        "failure_reason": null,
        "created_at": "2026-04-24T12:00:00+00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    }
  },
  "locale": "tr"
}
```

---

### 4.3 Custom Video Talep Detayı (Segmentlerle)

Belirli bir video talebinin detaylarını ve segment durumlarını getirir.

```
GET /api/v1/custom-video-requests/{uuid}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Request retrieved successfully",
  "data": {
    "request": {
      "uuid": "880e8400-e29b-41d4-a716-446655440003",
      "prompt": "Uzayda seyahat eden bir uzay gemisi",
      "input_image_path": "https://example.com/storage/custom-video-requests/input.jpg",
      "status": "processing",
      "token_cost": 200,
      "overall_progress": 50,
      "failure_reason": null,
      "segments": [
        {
          "id": 1,
          "segment_number": 1,
          "video_url": "https://example.com/storage/segments/segment-1.mp4",
          "status": "completed",
          "progress": 100,
          "failure_reason": null,
          "has_pending_edit": false,
          "latest_edit_request": null
        },
        {
          "id": 2,
          "segment_number": 2,
          "video_url": "https://example.com/storage/segments/segment-2.mp4",
          "status": "completed",
          "progress": 100,
          "failure_reason": null,
          "has_pending_edit": true,
          "latest_edit_request": {
            "id": 5,
            "edit_prompt": "Daha fazla yıldız ekle",
            "status": "pending",
            "admin_note": null,
            "created_at": "2026-04-24T12:10:00+00:00"
          }
        },
        {
          "id": 3,
          "segment_number": 3,
          "video_url": null,
          "status": "processing",
          "progress": 45,
          "failure_reason": null,
          "has_pending_edit": false,
          "latest_edit_request": null
        },
        {
          "id": 4,
          "segment_number": 4,
          "video_url": null,
          "status": "pending",
          "progress": 0,
          "failure_reason": null,
          "has_pending_edit": false,
          "latest_edit_request": null
        }
      ],
      "created_at": "2026-04-24T12:00:00+00:00"
    }
  },
  "locale": "tr"
}
```

**Segment Status Değerleri:**

- `pending`: Henüz işlem başlamadı
- `processing`: İşleniyor
- `completed`: Tamamlandı
- `failed`: Başarısız oldu

---

### 4.4 Segment Düzenleme Talebi Oluşturma

Tamamlanmış bir segment için düzenleme talebi oluşturur.

```
POST /api/v1/custom-video-requests/{uuid}/segments/{segmentId}/edit
```

**Headers:**

```
Authorization: Bearer {token}
```

**Request Body:**

```json
{
  "edit_prompt": "Daha fazla yıldız ekle ve renkleri daha canlı yap"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Edit request submitted successfully",
  "data": {
    "edit_request": {
      "id": 6,
      "segment_id": 2,
      "edit_prompt": "Daha fazla yıldız ekle ve renkleri daha canlı yap",
      "status": "pending",
      "created_at": "2026-04-24T12:15:00+00:00"
    }
  },
  "locale": "tr"
}
```

**Notlar:**

- Sadece `completed` durumundaki segmentler için düzenleme talebi oluşturulabilir
- Bir segment için aynı anda sadece bir bekleyen düzenleme talebi olabilir
- Düzenleme talepleri admin tarafından onaylandığında segment yeniden işlenir

---

### 4.5 Custom Video Talebi Silme

Henüz tamamlanmamış video talebini siler.

```
DELETE /api/v1/custom-video-requests/{uuid}
```

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Request deleted successfully",
  "data": [],
  "locale": "tr"
}
```

**Not:** `completed` durumundaki videolar silinemez.

---

## Validation Kuralları

### Register Request

| Alan                   | Tip    | Zorunlu | Validasyon                           |
| ---------------------- | ------ | ------- | ------------------------------------ |
| first_name             | string | Evet    | Max 255 karakter                     |
| last_name              | string | Evet    | Max 255 karakter                     |
| email                  | string | Evet    | Email formatı, unique                |
| password               | string | Evet    | Min 8 karakter                       |
| password_confirmation  | string | Evet    | password ile eşleşmeli               |
| country_code           | string | Evet    | Max 10 karakter                      |
| phone                  | string | Evet    | Max 20 karakter                      |

### Login Request

| Alan     | Tip    | Zorunlu | Validasyon   |
| -------- | ------ | ------- | ------------ |
| email    | string | Evet    | Email formatı |
| password | string | Evet    | -            |

### Generation Request (Custom Image)

| Alan        | Tip    | Zorunlu | Validasyon                                        |
| ----------- | ------ | ------- | ------------------------------------------------- |
| type        | string | Evet    | `custom_image`                                    |
| orientation | string | Evet    | `landscape`, `portrait`, `square`                 |
| description | string | Evet    | Max 1000 karakter                                 |
| input_image | file   | Hayır   | jpeg,png,jpg formatı, max 10MB                    |

### Generation Request (Template)

| Alan        | Tip    | Zorunlu | Validasyon                                        |
| ----------- | ------ | ------- | ------------------------------------------------- |
| type        | string | Evet    | `template_image`, `template_video`                |
| template_id | string | Evet    | Geçerli template UUID                             |
| orientation | string | Hayır   | `landscape`, `portrait`, `square`                 |
| input_image | file   | Evet    | jpeg,png,jpg formatı, max 10MB                    |

### Custom Video Request

| Alan        | Tip    | Zorunlu | Validasyon                              |
| ----------- | ------ | ------- | --------------------------------------- |
| prompt      | string | Evet    | -                                       |
| input_image | file   | Hayır   | jpeg,png,jpg,webp formatı, max 10MB     |

### Segment Edit Request

| Alan        | Tip    | Zorunlu | Validasyon |
| ----------- | ------ | ------- | ---------- |
| edit_prompt | string | Evet    | -          |

---

## HTTP Status Codes

| Code | Açıklama                                         |
| ---- | ------------------------------------------------ |
| 200  | Başarılı (OK)                                    |
| 201  | Oluşturuldu (Created)                            |
| 400  | Hatalı istek (Bad Request)                       |
| 401  | Yetkilendirme hatası (Unauthorized)              |
| 404  | Bulunamadı (Not Found)                           |
| 422  | İşlenemeyen veri (Unprocessable Entity)          |
| 500  | Sunucu hatası (Internal Server Error)            |