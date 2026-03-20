# RFID Parking Management System

Sistem monitoring parkir real-time berbasis RFID dengan Laravel, Filament Admin Panel, dan integrasi ESP32.

## Quick Start

```bash
# Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Create admin user
php artisan filament:user

# Run
php artisan serve
```

## Akses

| Module | URL | User |
|--------|-----|------|
| Dashboard | http://localhost:8000/parking | Public |
| Admin Panel | http://localhost:8000/admin | Login required |
| Login | http://localhost:8000/login | - |

## Features

✅ **Web Dashboard** - Check-in/out & real-time monitoring
✅ **Admin Panel** - Filament resource untuk RFID & parking sessions
✅ **API Endpoint** - `POST /api/rfid/scan` untuk hardware integration
✅ **Auto RFID Register** - Device otomatis terdaftar saat first scan
✅ **Dynamic Status** - NORMAL/OVERTIME/OUT computed on-the-fly
✅ **85-minute Duration** - Auto-expiry parking limit
✅ **Vehicle Tracking** - Owner info, vehicle number, phone

---

## API Integration

### Endpoint

```
POST /api/rfid/scan
Headers:
  - Content-Type: application/json
  - x-api-key: rfid-esp32-secret-key-2026

Body:
{
  "uid": "ABC123DEF456",
  "vehicle_number": "B 1234 ABC"  // optional
}
```

### Responses

**201 - Check-In:**
```json
{
  "action": "CHECK_IN",
  "status": "NORMAL",
  "rfid_uid": "ABC123DEF456",
  "vehicle_number": "B 1234 ABC",
  "check_in_at": "2026-03-19T10:00:00Z",
  "expires_at": "2026-03-19T11:25:00Z",
  "remaining_minutes": 85
}
```

**200 - Check-Out:**
```json
{
  "action": "CHECK_OUT",
  "status": "OUT",
  "rfid_uid": "ABC123DEF456",
  "vehicle_number": "B 1234 ABC",
  "check_out_at": "2026-03-19T10:20:00Z",
  "remaining_minutes": 0
}
```

### Test API

**cURL:**
```bash
curl -X POST http://localhost:8000/api/rfid/scan \
  -H "Content-Type: application/json" \
  -H "x-api-key: rfid-esp32-secret-key-2026" \
  -d '{"uid": "TEST-001"}'
```

**Python:**
```python
import requests
response = requests.post(
    'http://localhost:8000/api/rfid/scan',
    json={'uid': 'TEST-001'},
    headers={'x-api-key': 'rfid-esp32-secret-key-2026'}
)
print(response.json())
```

---

## Hardware Setup (ESP32 + RC522)

### Wiring

```
ESP32     RC522
GPIO 18 - SCK
GPIO 23 - MOSI
GPIO 19 - MISO
GPIO 5  - SDA (CS)
GPIO 4  - RST
```

### Libraries (Arduino IDE)
- ArduinoJson
- MFRC522

### Code

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

const char* WIFI_SSID = "YOUR_WIFI";
const char* WIFI_PASSWORD = "YOUR_PASSWORD";
const char* API_URL = "http://192.168.1.X:8000/api/rfid/scan";
const char* API_KEY = "rfid-esp32-secret-key-2026";

const int SDA_PIN = 5;
const int RST_PIN = 4;
MFRC522 rfid(SDA_PIN, RST_PIN);

void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();
  connectWiFi();
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    delay(100);
    return;
  }

  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }

  sendToAPI(uid);
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  delay(500);
}

void connectWiFi() {
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println(WiFi.localIP());
}

void sendToAPI(String uid) {
  HTTPClient http;
  http.begin(API_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("x-api-key", API_KEY);

  StaticJsonDocument<256> doc;
  doc["uid"] = uid;

  String payload;
  serializeJson(doc, payload);

  int code = http.POST(payload);
  Serial.println(code == 201 ? "✓ Checked In" : "✓ Checked Out");

  http.end();
}
```

---

## Admin Panel (Filament)

### RFID Devices
- **UID** - Unique identifier (required)
- **Device Name** - Optional label
- **Owner Name** - Vehicle owner
- **Vehicle Number** - License plate (B 1234 ABC)
- **Phone** - Contact number
- **Notes** - Additional info
- **Active** - Enable/disable device
- **Total Sessions** - Count of all parking sessions

### Parking Sessions
- View all sessions (active/checked-out)
- Filter by device, status, date range
- See remaining time, overtime status
- Linked to RFID device info

---

## Database Schema

### RFID Table
```sql
rfids (id, uid*, name, owner_name, vehicle_number, phone, notes, is_active, timestamps)
```
*unique

### Parking Sessions Table
```sql
parking_sessions (
  id, rfid_id (FK), rfid_uid,
  check_in_at, expires_at, check_out_at,
  timestamps
)
```
Unique: (rfid_id, check_out_at) - prevents duplicate active sessions, allows reuse after checkout

---

## Key Behaviors

| Scenario | Behavior |
|----------|----------|
| First RFID scan | Auto-create RFID device + check-in |
| Second scan same RFID | Check-out (if active session) |
| Third scan same RFID | Check-in (session reusable after checkout) |
| Status NORMAL | Time remaining > 0 |
| Status OVERTIME | Time remaining < 0 (passed expires_at) |
| Status OUT | check_out_at is not null |

---

## Configuration

**.env**
```
RFID_API_KEY=rfid-esp32-secret-key-2026
APP_TIMEZONE=Asia/Jakarta
```

---

## Commands

```bash
# Run tests
php artisan test tests/Feature/RfidApiTest.php

# View routes
php artisan route:list | grep rfid

# Create admin
php artisan filament:user

# Reset database
php artisan migrate:fresh

# Optimize
php artisan optimize
```

---

## Testing

10/10 tests passing covering:
- Check-in/out flow
- API key validation
- RFID reuse after checkout
- Overtime status computation
- Vehicle number support

---

## Architecture

```
Routes (web.php, api.php)
├─ GET /parking → Dashboard (public)
├─ POST /login → Login form
├─ POST /logout → Logout
├─ POST /api/rfid/scan → Hardware API
└─ /admin/* → Filament admin

Controllers
├─ ParkingSessionController → Dashboard logic
├─ AuthController → Login/logout
└─ RfidController → API endpoint

Models
├─ Rfid (hasMany ParkingSessions)
├─ ParkingSession (belongsTo Rfid)
└─ User (Filament admin)

Filament Resources
├─ RfidResource → RFID device management
└─ ParkingSessionResource → Session history
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| RFID not appearing | Scan via ESP32 or API first |
| 403 API error | Check RFID_API_KEY in .env |
| Can't login | Run `php artisan filament:user` |
| Tests failing | Run `php artisan migrate:fresh` then tests |
| ESP32 no WiFi | Check SSID/password, increase retries |

---

## Production Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Update `RFID_API_KEY` to secure value
- [ ] Configure `APP_URL` and timezone
- [ ] Run `php artisan optimize`
- [ ] Test API endpoint with actual hardware
- [ ] Review & backup `.env`
- [ ] Run tests: `php artisan test`

---

## Tech Stack

- Laravel 13 + Filament 5.4
- PHP 8.3+
- SQLite / MySQL
- Tailwind CSS
- ArduinoJson / MFRC522
