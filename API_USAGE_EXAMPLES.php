<?php

/**
 * API Usage Examples
 *
 * This file demonstrates how to interact with the RFID Parking API
 * using curl, PHP, JavaScript, or other HTTP clients.
 */

// ============================================================================
// EXAMPLE 1: PHP cURL
// ============================================================================

/*
$rfidUid = 'RFID-TEST-001';
$apiKey = 'rfid-esp32-secret-key-2026';
$apiUrl = 'http://localhost:8000/api/rfid/scan';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $apiKey,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'uid' => $rfidUid,
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
echo "HTTP Status: " . $httpCode . "\n";
echo "Action: " . $data['action'] . "\n";
echo "Status: " . $data['status'] . "\n";
echo "Remaining Minutes: " . $data['remaining_minutes'] . "\n";
*/


// ============================================================================
// EXAMPLE 2: Using Guzzle HTTP Client (Recommended)
// ============================================================================

/*
use GuzzleHttp\Client;

$client = new Client();
$apiKey = 'rfid-esp32-secret-key-2026';

try {
    $response = $client->post('http://localhost:8000/api/rfid/scan', [
        'json' => ['uid' => 'RFID-TEST-001'],
        'headers' => [
            'x-api-key' => $apiKey,
        ],
    ]);

    $data = json_decode($response->getBody(), true);

    echo "Action: " . $data['action'] . "\n";
    echo "Status: " . $data['status'] . "\n";
    echo "Expires: " . $data['expires_at'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/


// ============================================================================
// EXAMPLE 3: Using Symfony HttpClient
// ============================================================================

/*
use Symfony\Contracts\HttpClient\HttpClientInterface;

$httpClient = HttpClientFactory::create();

$response = $httpClient->request('POST', 'http://localhost:8000/api/rfid/scan', [
    'json' => ['uid' => 'RFID-TEST-001'],
    'headers' => [
        'x-api-key' => 'rfid-esp32-secret-key-2026',
    ],
]);

$data = $response->toArray();
echo $data['action']; // CHECK_IN or CHECK_OUT
echo $data['status'];  // NORMAL, OVERTIME, or OUT
*/


// ============================================================================
// EXAMPLE 4: Laravel HTTP Client (if available)
// ============================================================================

/*
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'x-api-key' => 'rfid-esp32-secret-key-2026',
])->post('http://localhost:8000/api/rfid/scan', [
    'uid' => 'RFID-TEST-001',
]);

if ($response->successful()) {
    $data = $response->json();
    echo $data['action'];     // CHECK_IN or CHECK_OUT
    echo $data['status'];     // NORMAL, OVERTIME, or OUT
    echo $data['expires_at']; // ISO8601 datetime
} else {
    echo "Error: " . $response->status();
}
*/


// ============================================================================
// EXAMPLE 5: cURL Command Line (for testing)
// ============================================================================

/*
curl -X POST http://localhost:8000/api/rfid/scan \
  -H "Content-Type: application/json" \
  -H "x-api-key: rfid-esp32-secret-key-2026" \
  -d '{"uid": "RFID-TEST-001"}'

# Response (CHECK-IN):
# {
#   "action": "CHECK_IN",
#   "status": "NORMAL",
#   "rfid_uid": "RFID-TEST-001",
#   "check_in_at": "2026-03-18T13:21:00Z",
#   "expires_at": "2026-03-18T14:21:00Z",
#   "remaining_minutes": 60
# }

# Second call (CHECK-OUT):
# {
#   "action": "CHECK_OUT",
#   "status": "OUT",
#   "rfid_uid": "RFID-TEST-001",
#   "check_in_at": "2026-03-18T13:21:00Z",
#   "expires_at": "2026-03-18T14:21:00Z",
#   "check_out_at": "2026-03-18T13:25:00Z",
#   "remaining_minutes": 0
# }
*/


// ============================================================================
// EXAMPLE 6: JavaScript Fetch API
// ============================================================================

/*
async function scanRfid(uid) {
    const apiUrl = 'http://localhost:8000/api/rfid/scan';
    const apiKey = 'rfid-esp32-secret-key-2026';

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': apiKey,
            },
            body: JSON.stringify({ uid: uid }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        console.log('Action:', data.action);       // CHECK_IN or CHECK_OUT
        console.log('Status:', data.status);       // NORMAL, OVERTIME, OUT
        console.log('Remaining:', data.remaining_minutes); // minutes

        if (data.action === 'CHECK_IN') {
            console.log('✓ Vehicle checked in');
        } else {
            console.log('✓ Vehicle checked out');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Usage
scanRfid('RFID-TEST-001');
*/


// ============================================================================
// EXAMPLE 7: Python Requests
// ============================================================================

/*
import requests
import json

api_url = 'http://localhost:8000/api/rfid/scan'
api_key = 'rfid-esp32-secret-key-2026'

response = requests.post(
    api_url,
    headers={
        'Content-Type': 'application/json',
        'x-api-key': api_key,
    },
    json={'uid': 'RFID-TEST-001'}
)

if response.status_code in [200, 201]:
    data = response.json()
    print(f"Action: {data['action']}")
    print(f"Status: {data['status']}")
    print(f"Remaining: {data['remaining_minutes']} minutes")
else:
    print(f"Error: {response.status_code}")
    print(response.json())
*/


// ============================================================================
// EXAMPLE 8: Node.js with Axios
// ============================================================================

/*
const axios = require('axios');

async function scanRfid(uid) {
    try {
        const response = await axios.post(
            'http://localhost:8000/api/rfid/scan',
            { uid: uid },
            {
                headers: {
                    'x-api-key': 'rfid-esp32-secret-key-2026',
                },
            }
        );

        console.log('Response:', response.data);
        // {
        //   action: 'CHECK_IN',
        //   status: 'NORMAL',
        //   rfid_uid: 'RFID-TEST-001',
        //   remaining_minutes: 60,
        //   ...
        // }
    } catch (error) {
        console.error('Error:', error.response?.data || error.message);
    }
}

scanRfid('RFID-TEST-001');
*/


// ============================================================================
// API RESPONSE EXAMPLES
// ============================================================================

/*

SUCCESS - CHECK-IN (201 Created)
---------------------------------
{
  "action": "CHECK_IN",
  "status": "NORMAL",
  "rfid_uid": "RFID-TEST-001",
  "check_in_at": "2026-03-18T13:21:00Z",
  "expires_at": "2026-03-18T14:21:00Z",
  "remaining_minutes": 60
}


SUCCESS - CHECK-OUT (200 OK)
----------------------------
{
  "action": "CHECK_OUT",
  "status": "OUT",
  "rfid_uid": "RFID-TEST-001",
  "check_in_at": "2026-03-18T13:21:00Z",
  "expires_at": "2026-03-18T14:21:00Z",
  "check_out_at": "2026-03-18T13:25:00Z",
  "remaining_minutes": 0
}


ERROR - Missing API Key (403 Forbidden)
----------------------------------------
{
  "error": "Unauthorized",
  "message": "Invalid or missing API key"
}


ERROR - Invalid Request (422 Unprocessable Entity)
---------------------------------------------------
{
  "message": "The uid field is required.",
  "errors": {
    "uid": ["The uid field is required."]
  }
}

*/


// ============================================================================
// API ENDPOINTS
// ============================================================================

/*
POST /api/rfid/scan

  Required Headers:
    - Content-Type: application/json
    - x-api-key: rfid-esp32-secret-key-2026

  Request Body:
    {
      "uid": "RFID_UID_STRING"
    }

  Success Responses:
    - 201 Created: New session created (CHECK-IN)
    - 200 OK: Session updated (CHECK-OUT)

  Error Responses:
    - 403 Forbidden: Missing or invalid API key
    - 422 Unprocessable Entity: Validation errors
    - 409 Conflict: Session creation conflict
*/


// ============================================================================
// ENVIRONMENT VARIABLES (.env)
// ============================================================================

/*
RFID_API_KEY=rfid-esp32-secret-key-2026

# Or customize the key in .env:
RFID_API_KEY=your-custom-secure-key-here
*/
