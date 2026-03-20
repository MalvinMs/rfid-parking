/**
 * ESP32 RFID Parking System - API Integration Example
 *
 * Hardware Required:
 * - ESP32 (e.g., ESP32-WROOM-32)
 * - RC522 RFID Reader (SPI mode)
 * - Wiring:
 *   RFID SCK   → GPIO 18
 *   RFID MOSI  → GPIO 23
 *   RFID MISO  → GPIO 19
 *   RFID SDA   → GPIO 5 (CS)
 *   RFID RST   → GPIO 4
 *
 * Libraries Required:
 * - WiFi.h (built-in)
 * - HTTPClient.h (built-in)
 * - ArduinoJson.h (install via Arduino Library Manager)
 * - MFRC522.h (install via Arduino Library Manager)
 *
 * Configuration:
 * - Update WIFI_SSID and WIFI_PASSWORD
 * - Update API_SERVER_URL and API_KEY
 * - Adjust I2C/SPI pins if different
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

// WiFi Configuration
const char* WIFI_SSID = "YOUR_SSID";
const char* WIFI_PASSWORD = "YOUR_PASSWORD";

// API Configuration
const char* API_SERVER_URL = "http://192.168.1.100:8000/api/rfid/scan";
const char* API_KEY = "rfid-esp32-secret-key-2026";

// RFID Configuration
const int SDA_PIN = 5;
const int RST_PIN = 4;
MFRC522 rfidReader(SDA_PIN, RST_PIN);

// Status LED pins
const int LED_CHECK_IN = 12;   // Green LED for check-in
const int LED_CHECK_OUT = 13;  // Blue LED for check-out
const int LED_ERROR = 15;      // Red LED for error

void setup()
{
    Serial.begin(115200);
    delay(1000);

    Serial.println("\n\n");
    Serial.println("========================================");
    Serial.println("ESP32 RFID Parking System - Hardware API");
    Serial.println("========================================");

    // Initialize LED pins
    pinMode(LED_CHECK_IN, OUTPUT);
    pinMode(LED_CHECK_OUT, OUTPUT);
    pinMode(LED_ERROR, OUTPUT);
    digitalWrite(LED_CHECK_IN, LOW);
    digitalWrite(LED_CHECK_OUT, LOW);
    digitalWrite(LED_ERROR, LOW);

    // Initialize SPI and RFID
    SPI.begin();
    rfidReader.PCD_Init();

    Serial.println("RFID Reader initialized");
    Serial.println("Waiting for WiFi connection...");

    // Connect to WiFi
    connectToWiFi();

    Serial.println("Setup complete!");
    Serial.println("Ready to scan RFID tags...\n");
}

void loop()
{
    // Look for new RFID cards
    if (!rfidReader.PICC_IsNewCardPresent())
    {
        delay(100);
        return;
    }

    // Select one of the cards
    if (!rfidReader.PICC_ReadCardSerial())
    {
        delay(100);
        return;
    }

    // Get RFID UID
    String uid = getRfidUid();

    Serial.print("RFID Tag Scanned: ");
    Serial.println(uid);

    // Send to API
    sendRfidScan(uid);

    // Halt PICC
    rfidReader.PICC_HaltA();
    rfidReader.PCD_StopCrypto1();

    delay(500);
}

/**
 * Connect ESP32 to WiFi
 */
void connectToWiFi()
{
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20)
    {
        delay(500);
        Serial.print(".");
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED)
    {
        Serial.println("\nWiFi connected!");
        Serial.print("IP: ");
        Serial.println(WiFi.localIP());
    }
    else
    {
        Serial.println("\nFailed to connect to WiFi");
    }
}

/**
 * Get RFID UID as hex string
 */
String getRfidUid()
{
    String uid = "";
    for (byte i = 0; i < rfidReader.uid.size; i++)
    {
        if (rfidReader.uid.uidByte[i] < 0x10)
            uid += "0";
        uid += String(rfidReader.uid.uidByte[i], HEX);
    }
    uid.toUpperCase();
    return uid;
}

/**
 * Send RFID scan to API
 *
 * API Endpoint: POST /api/rfid/scan
 * Headers: x-api-key: <API_KEY>
 * Body: { "uid": "RFID_UID" }
 *
 * Response:
 * {
 *   "action": "CHECK_IN" or "CHECK_OUT",
 *   "status": "NORMAL" or "OVERTIME" or "OUT",
 *   "rfid_uid": "RFID_UID",
 *   "expires_at": "2026-03-18T13:21:00Z",
 *   "remaining_minutes": 60
 * }
 */
void sendRfidScan(String uid)
{
    if (WiFi.status() != WL_CONNECTED)
    {
        Serial.println("WiFi not connected");
        blinkLed(LED_ERROR, 3, 200);
        return;
    }

    HTTPClient http;
    http.begin(API_SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("x-api-key", API_KEY);

    // Create JSON payload
    StaticJsonDocument<256> doc;
    doc["uid"] = uid;

    String payload;
    serializeJson(doc, payload);

    Serial.print("Sending to API: ");
    Serial.println(payload);

    // Send POST request
    int httpResponseCode = http.POST(payload);

    if (httpResponseCode > 0)
    {
        String response = http.getString();

        Serial.print("API Response Code: ");
        Serial.println(httpResponseCode);
        Serial.print("Response: ");
        Serial.println(response);

        // Parse response
        StaticJsonDocument<512> responseDoc;
        DeserializationError error = deserializeJson(responseDoc, response);

        if (!error)
        {
            String action = responseDoc["action"] | "ERROR";
            String status = responseDoc["status"] | "ERROR";

            Serial.print("Action: ");
            Serial.println(action);
            Serial.print("Status: ");
            Serial.println(status);

            // Handle response
            if (action == "CHECK_IN")
            {
                Serial.println("✓ Vehicle checked in successfully");
                blinkLed(LED_CHECK_IN, 2, 300);
            }
            else if (action == "CHECK_OUT")
            {
                Serial.println("✓ Vehicle checked out successfully");
                blinkLed(LED_CHECK_OUT, 2, 300);
            }
            else
            {
                Serial.println("✗ Error from API");
                blinkLed(LED_ERROR, 3, 200);
            }
        }
        else
        {
            Serial.print("JSON Parse Error: ");
            Serial.println(error.c_str());
            blinkLed(LED_ERROR, 5, 100);
        }
    }
    else
    {
        Serial.print("HTTP Error: ");
        Serial.println(httpResponseCode);
        blinkLed(LED_ERROR, 3, 200);
    }

    http.end();
}

/**
 * Blink LED for visual feedback
 */
void blinkLed(int pin, int times, int delayMs)
{
    for (int i = 0; i < times; i++)
    {
        digitalWrite(pin, HIGH);
        delay(delayMs);
        digitalWrite(pin, LOW);
        delay(delayMs);
    }
}
