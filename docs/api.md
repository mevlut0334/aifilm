# API

## Base URL

```
/api/v1/
```

## Auth

Laravel Sanctum Bearer Token

```http
Authorization: Bearer {token}
```

---

## Endpointler

### Auth

```
POST /register
POST /login
POST /logout
GET  /profile
```

### Generation Requests

```
POST   /generation-requests
GET    /generation-requests
GET    /generation-requests/{uuid}
DELETE /generation-requests/{uuid}
POST   /generation-requests/{uuid}/recreate
```

POST body örneği (custom görsel):

```json
{
  "type": "custom_image",
  "orientation": "landscape",
  "description": "Fütüristik şehir manzarası, gün batımı ışığı"
}
```

`orientation` zorunludur: `landscape`, `portrait`, `square`

### Templates

```
GET /templates
GET /templates/{uuid}
GET /templates?orientation=landscape
```

`orientation` query parametresi ile filtreleme yapılabilir.

### Blog

```
GET /blog-posts
GET /blog-posts/{uuid}
```

---

## Response Standardı

```json
{
  "success": true,
  "message": "Request created",
  "data": {},
  "locale": "en"
}
```

`locale` alanı her response'da döner. Desteklenen değerler: `en`, `tr`

---

## Form Request Örneği

`GenerationRequestStoreRequest`:

```php
public function rules(): array
{
    return [
        'type'        => ['required', Rule::in(['custom_image', 'custom_video', 'template_image', 'template_video'])],
        'orientation' => ['required_if:type,custom_image', Rule::in(['landscape', 'portrait', 'square'])],
        'description' => ['required_if:type,custom_image', 'string', 'max:1000'],
        'template_id' => ['required_if:type,template_image,template_video', 'exists:templates,uuid'],
    ];
}
```