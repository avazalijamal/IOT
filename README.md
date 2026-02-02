# Su Sürətləri IoT API — README

Bu sənəd `su_suretleri` cədvəli üçün hazırlanan **insert / get / update / delete** endpoint-lərini izah edir. API PHP (PDO) və MySQL üzərində qurulub, JSON cavablar qaytarır.

---

## Baza URL

```
https://hewart.io/iot/api/
```

---

## Məlumat modeli (DB sxemi)

Cədvəl: `su_suretleri`

```sql
CREATE TABLE su_suretleri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sethi_suret FLOAT NOT NULL,
  orta_suret FLOAT NOT NULL,
  tezlik FLOAT NOT NULL,
  tarix TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

> `tarix` sahəsi avtomatik `CURRENT_TIMESTAMP` (NOW) yazır.

### DB bağlantısı

`db.php` faylında PDO bağlantısı saxlanılır və bütün endpoint-lər `require_once "db.php";` vasitəsilə bu faylı istifadə edir.

```php
<?php
// db.php (nümunə — real dəyərləri serverdə saxlayın, repoya əlavə etməyin)
$host = "localhost";
$db   = "u879108216_iot";
$user = "u879108216_iot";
$pass = "***************"; // real parolu burada saxlayın

$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo  = new PDO($dsn, $user, $pass, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]);
```
> Təhlükəsizlik: parolu repoya commit etməyin; mümkün olsa `.env` və ya server secret-lərdən istifadə edin.

---

## Endpoint-lər

### 1) Məlumat əlavə et (INSERT)

- **URL:** `POST /api_insert.php`
- **Headers:** `Content-Type: application/json`
- **Body (JSON):**
  ```json
  {
    "sethi_suret": 1.25,
    "orta_suret": 0.98,
    "tezlik": 60.5
  }
  ```
- **Cavab (201):**
  ```json
  { "success": true, "id": 123, "message": "Məlumat əlavə olundu" }
  ```

**cURL:**
```bash
curl -X POST https://hewart.io/iot/api/api_insert.php   -H "Content-Type: application/json"   -d '{"sethi_suret":1.25,"orta_suret":0.98,"tezlik":60.5}'
```

---

### 2) Məlumat oxu (GET  — siyahı və ya tək qeyd)

- **URL:** `GET /api_get.php`
- **Parametrlər:**
  - `id` *(opsional)* — verilsə, yalnız həmin qeyd qaytarılır  
  - `limit` *(opsional, default 100, max 500)*
  - `offset` *(opsional, default 0)*
  - `order` *(opsional: `asc` | `desc`, default `desc`)*
- **Path param dəstəyi:** `GET /api_get.php/7` (serverdə `PATH_INFO` aktivdirsə)

**Nümunə — tək qeyd:**
```
GET https://hewart.io/iot/api/api_get.php?id=7
```

**Cavab (200):**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "sethi_suret": 1.25,
    "orta_suret": 0.98,
    "tezlik": 60.5,
    "tarix": "2025-08-31 12:01:03"
  }
}
```

**Nümunə — siyahı:**
```
GET https://hewart.io/iot/api/api_get.php?limit=50&offset=0&order=desc
```

**Cavab (200):**
```json
{
  "success": true,
  "count": 2,
  "limit": 50,
  "offset": 0,
  "order": "desc",
  "data": [
    { "id": 12, "sethi_suret": 1.40, "orta_suret": 1.10, "tezlik": 64.8, "tarix": "2025-08-31 12:04:21" },
    { "id": 11, "sethi_suret": 1.25, "orta_suret": 0.98, "tezlik": 60.5, "tarix": "2025-08-31 12:01:03" }
  ]
}
```

**cURL:**
```bash
curl "https://hewart.io/iot/api/api_get.php?limit=20&order=desc"
```

---

### 3) Məlumat yenilə (UPDATE — qismən və ya tam)

- **URL:** `PUT /api_update.php` və ya `PATCH /api_update.php`
- **ID ötürmə yolları:** `?id=7` **və ya** `/api_update.php/7` **və ya** body-də `"id": 7`
- **Headers:** `Content-Type: application/json`
- **Body (JSON):** istənilən sahələri qismən göndərə bilərsiniz
  ```json
  { "tezlik": 72.4 }
  ```
  və ya
  ```json
  {
    "sethi_suret": 1.42,
    "orta_suret": 1.10,
    "tezlik": 68.9
  }
  ```
- **Cavab (200):**
  ```json
  { "success": true, "message": "Qeyd yeniləndi", "data": { ...yenilənmiş qeyd... } }
  ```

**cURL (qismən):**
```bash
curl -X PATCH "https://hewart.io/iot/api/api_update.php?id=7"   -H "Content-Type: application/json"   -d '{"tezlik":72.4}'
```

---

### 4) Məlumat sil (DELETE)

- **URL:** `DELETE /api_delete.php`
- **Davranış:**
  - `id` **gələrsə** → yalnız həmin qeyd silinir
  - `id` **gəlməzsə** → **bütün cədvəl silinir** *(diqqətli olun!)*
- **ID ötürmə yolları:** `?id=7` və ya `/api_delete.php/7`

**Nümunə — tək qeyd:**
```
DELETE https://hewart.io/iot/api/api_delete.php?id=7
```

**Cavab (200):**
```json
{ "success": true, "message": "ID=7 olan qeyd silindi" }
```

**Nümunə — hamısını silmək (id YOXDUR):**
```
DELETE https://hewart.io/iot/api/api_delete.php
```

**Cavab (200):**
```json
{ "success": true, "message": "Bütün qeyd(lər) silindi" }
```

> Təhlükəsizlik tövsiyəsi: istehsalda **hamısını silmə** əməliyyatı üçün əlavə təsdiq (`confirm=true`), IP allowlist və ya token tələb etmək tövsiyə olunur.

---

## HTTP Status kodları

| Kod | İzah |
|-----|-----|
| 200 | Uğurlu (GET/UPDATE/DELETE) |
| 201 | Uğurlu yaradıldı (INSERT) |
| 400 | Yanlış sorğu (Content-Type/JSON/id səhv) |
| 404 | Tapılmadı (məs. id mövcud deyil) |
| 405 | Metod icazəli deyil |
| 422 | Validasiya xətası (rəqəm olmayan dəyərlər və s.) |
| 500 | Server/DB xətası |

---

## CORS

Bütün endpoint-lərdə CORS başlıqları var:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: [uyğun metodlar]
Access-Control-Allow-Headers: Content-Type, Authorization
```
`OPTIONS` preflight sorğuları üçün 204 qaytarılır.

---

## Fayl quruluşu (təklif)

```
/api
  ├── db.php
  ├── api_insert.php
  ├── api_get.php
  ├── api_update.php
  └── api_delete.php
```

---

## Postman qısa təlimatı

1. **Request** yaradın → `{{baseurl}}` olaraq `https://hewart.io/iot/api/`.
2. **Headers**: `Content-Type: application/json` (POST/PUT/PATCH üçün).
3. Body → **raw JSON** (insert/update).
4. GET/DELETE üçün lazımi `Params` (`id`, `limit`, …) əlavə edin.

---

## ESP32/Arduino-dan nümunə (opsional)

```cpp
// Arduino (ESP32) HTTP POST nümunəsi
#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "SSID";
const char* pass = "PASSWORD";
const char* url  = "https://hewart.io/iot/api/api_insert.php";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("WiFi OK");

  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");

  String body = R"({"sethi_suret":1.25,"orta_suret":0.98,"tezlik":60.5})";
  int code = http.POST(body);
  Serial.printf("HTTP %d\n", code);
  Serial.println(http.getString());

  http.end();
}

void loop() {}
```

---

## Tətbiq tələbləri

- **PHP 8.0+**
- **PDO MySQL** genişləndirilməsi aktiv
- **MySQL 5.7+ / 8.0+**
- HTTPS (tövsiyə olunur)

---

## Təhlükəsizlik və yaxşı təcrübələr

- DB parollarını repoya **commit etməyin** (dotenv/secret manager istifadə edin).
- `DELETE`-də hamısını silmə əməliyyatını **mütləq qoruyun** (token, confirm param, IP allowlist).
- Serverdə **HTTPS** aktiv olsun.
- Zəruri hallarda **Auth token** (`Authorization: Bearer <TOKEN>`) əlavə edin və endpoint-lərdə yoxlayın.
- Giriş dəyərləri üçün əlavə **rate limiting** və **loglama** faydalıdır.

---

Hər hansı genişləndirmə (tarix aralığı filtrləri, axtarış, auth, pagination meta və s.) istəsən, mən əlavə edib README-ni də yeniləyə bilərəm.

# 🖥️ UI (Web Panel) --- İstifadəçi İnterfeysi

Bu sənəd `su_suretleri` API-si ilə işləyən veb əsaslı istifadəçi
interfeysinin (dashboard) quruluşunu və funksiyalarını izah edir. UI
əsasən məlumatların **vizual izlənməsi**, **əlavə edilməsi**,
**redaktəsi** və **silinməsi** üçün nəzərdə tutulur.

------------------------------------------------------------------------

## 📊 Dashboard (Ümumi Baxış)

**Təsviri:** - Son ölçmələrin qrafikləri (sethi_suret, orta_suret,
tezlik)\
- Son 24 saat / 7 gün / 30 gün filtrləri\
- Ümumi qeyd sayı və son yenilənmə vaxtı

**Ekran görüntüsü əlavə edin:** docs/images/dashboard.png

------------------------------------------------------------------------

## ➕ Məlumat Əlavə Etmə Formu

**Təsviri:** - Səthi sürət, orta sürət və tezlik üçün input sahələri\
- "Göndər" düyməsi → POST /api_insert.php\
- Uğurlu və ya xəta mesajlarının göstərilməsi

**Ekran görüntüsü əlavə edin:** docs/images/insert_form.png

------------------------------------------------------------------------

## 📋 Məlumat Siyahısı (Cədvəl Görünüşü)

**Təsviri:** - ID, sethi_suret, orta_suret, tezlik, tarix sütunları\
- Axtarış, sıralama (ASC/DESC), səhifələmə (pagination)\
- Hər sətirdə Edit və Delete düymələri

![Dashboard](/presentation/home.png)

------------------------------------------------------------------------

## ✏️ Yeniləmə və Silmə Pəncərələri

**Təsviri:** - Edit → PATCH /api_update.php\
- Delete → DELETE /api_delete.php\
- Təsdiq (confirmation) pəncərəsi

![Dashboard](/presentation/insert.png)

------------------------------------------------------------------------

## 🔐 UI üçün Tövsiyələr

-   UI yalnız HTTPS üzərindən işləsin\
-   Lazım gələrsə login sistemi əlavə edilsin\
-   API token istifadə olunarsa, UI header-lara avtomatik əlavə etsin

------------------------------------------------------------------------

## 📁 Tövsiyə edilən qovluq strukturu

/docs\
/images\
dashboard.png\
insert_form.png\
table_view.png\
edit_modal.png\
delete_confirm.png

UI.md

