<?php

namespace Tests\Feature;

use App\Models\ParkingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfidApiTest extends TestCase
{
  use RefreshDatabase;

  protected string $apiKey = 'rfid-esp32-secret-key-2026';

  /**
   * Test check-in via API: First scan creates new session
   */
  public function test_rfid_check_in_creates_new_session(): void
  {
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TEST-001',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response->assertStatus(201)
      ->assertJson([
        'action' => 'CHECK_IN',
        'status' => 'NORMAL',
        'rfid_uid' => 'RFID-TEST-001',
      ])
      ->assertJsonStructure([
        'action',
        'status',
        'rfid_uid',
        'check_in_at',
        'expires_at',
        'remaining_minutes',
      ]);

    $this->assertDatabaseHas('parking_sessions', [
      'rfid_uid' => 'RFID-TEST-001',
      'check_out_at' => null,
    ]);
  }

  /**
   * Test check-out via API: Second scan checks out existing session
   */
  public function test_rfid_check_out_existing_session(): void
  {
    // Create RFID device first
    $rfid = \App\Models\Rfid::create([
      'uid' => 'RFID-TEST-002',
      'name' => 'Test Device',
      'is_active' => true,
    ]);

    // Create active session
    $session = ParkingSession::create([
      'rfid_id' => $rfid->id,
      'rfid_uid' => 'RFID-TEST-002',
      'check_in_at' => now(),
      'expires_at' => now()->addMinutes(85),
    ]);

    // Second scan should check out
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TEST-002',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response->assertStatus(200)
      ->assertJson([
        'action' => 'CHECK_OUT',
        'status' => 'OUT',
        'rfid_uid' => 'RFID-TEST-002',
      ])
      ->assertJsonStructure([
        'action',
        'status',
        'rfid_uid',
        'check_in_at',
        'expires_at',
        'check_out_at',
        'remaining_minutes',
      ]);

    $this->assertDatabaseHas('parking_sessions', [
      'rfid_uid' => 'RFID-TEST-002',
    ]);

    // Verify check_out_at is not null
    $session = ParkingSession::where('rfid_uid', 'RFID-TEST-002')->first();
    $this->assertNotNull($session->check_out_at);
  }

  /**
   * Test API rejects missing API key
   */
  public function test_rfid_api_requires_api_key(): void
  {
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TEST-003',
    ]);

    $response->assertStatus(403)
      ->assertJson([
        'error' => 'Unauthorized',
      ]);
  }

  /**
   * Test API rejects invalid API key
   */
  public function test_rfid_api_rejects_invalid_key(): void
  {
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TEST-004',
    ], [
      'x-api-key' => 'invalid-key',
    ]);

    $response->assertStatus(403)
      ->assertJson([
        'error' => 'Unauthorized',
      ]);
  }

  /**
   * Test API validates required uid field
   */
  public function test_rfid_api_requires_uid(): void
  {
    $response = $this->postJson('/api/rfid/scan', [], [
      'x-api-key' => $this->apiKey,
    ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors('uid');
  }

  /**
   * Test toggle scenario: check-in, check-out, check-in again with different RFID
   */
  public function test_rfid_toggle_scenario(): void
  {
    // First scan: Check-in with RFID 1
    $response1 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TOGGLE-1',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response1->assertStatus(201)
      ->assertJson(['action' => 'CHECK_IN']);

    $this->assertDatabaseCount('parking_sessions', 1);

    // Second scan: Check-out
    $response2 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TOGGLE-1',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response2->assertStatus(200)
      ->assertJson(['action' => 'CHECK_OUT']);

    // Third scan: Check-in with RFID 2
    $response3 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-TOGGLE-2',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response3->assertStatus(201)
      ->assertJson(['action' => 'CHECK_IN']);

    // Should have 2 sessions now
    $this->assertDatabaseCount('parking_sessions', 2);
  }

  /**
   * Test status computation for overtime session
   */
  public function test_rfid_overtime_status_in_response(): void
  {
    // Create RFID device
    $rfid = \App\Models\Rfid::create([
      'uid' => 'RFID-OVERTIME',
      'name' => 'Overtime Test',
      'is_active' => true,
    ]);

    // Create session that's already expired
    $session = ParkingSession::create([
      'rfid_id' => $rfid->id,
      'rfid_uid' => 'RFID-OVERTIME',
      'check_in_at' => now()->subHours(2),
      'expires_at' => now()->subMinutes(30),
    ]);

    // Scan to check out
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-OVERTIME',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response->assertStatus(200)
      ->assertJson([
        'action' => 'CHECK_OUT',
        'status' => 'OUT', // Status is OUT because checked out
      ]);
  }

  /**
   * Test check-in with same RFID after checkout (should work)
   */
  public function test_rfid_check_in_after_checkout_same_rfid(): void
  {
    // First scan: Check-in
    $response1 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-REUSE',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response1->assertStatus(201)
      ->assertJson(['action' => 'CHECK_IN']);

    // Second scan: Check-out
    $response2 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-REUSE',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response2->assertStatus(200)
      ->assertJson(['action' => 'CHECK_OUT']);

    // Third scan with SAME RFID: Should check-in again
    $response3 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-REUSE',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response3->assertStatus(201)
      ->assertJson([
        'action' => 'CHECK_IN',
        'status' => 'NORMAL',
        'rfid_uid' => 'RFID-REUSE',
      ]);

    // Should have 2 sessions (first check-in/out, second check-in)
    $this->assertDatabaseCount('parking_sessions', 2);
  }

  /**
   * Test API supports optional vehicle_number field
   */
  public function test_rfid_api_supports_vehicle_number(): void
  {
    // Check-in with vehicle number
    $response1 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-VEHICLE-TEST',
      'vehicle_number' => 'B 1234 ABC',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response1->assertStatus(201)
      ->assertJson([
        'action' => 'CHECK_IN',
        'rfid_uid' => 'RFID-VEHICLE-TEST',
        'vehicle_number' => 'B 1234 ABC',
      ]);

    // Verify vehicle_number is stored in RFID, not ParkingSession
    $this->assertDatabaseHas('rfids', [
      'uid' => 'RFID-VEHICLE-TEST',
      'vehicle_number' => 'B 1234 ABC',
    ]);

    // Check-out should include vehicle_number
    $response2 = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-VEHICLE-TEST',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response2->assertStatus(200)
      ->assertJson([
        'action' => 'CHECK_OUT',
        'rfid_uid' => 'RFID-VEHICLE-TEST',
        'vehicle_number' => 'B 1234 ABC',
      ]);
  }

  /**
   * Test API vehicle_number is optional
   */
  public function test_rfid_api_vehicle_number_is_optional(): void
  {
    // Check-in without vehicle number
    $response = $this->postJson('/api/rfid/scan', [
      'uid' => 'RFID-NO-VEHICLE',
    ], [
      'x-api-key' => $this->apiKey,
    ]);

    $response->assertStatus(201)
      ->assertJson([
        'action' => 'CHECK_IN',
        'rfid_uid' => 'RFID-NO-VEHICLE',
        'vehicle_number' => null,
      ]);

    $this->assertDatabaseHas('parking_sessions', [
      'rfid_uid' => 'RFID-NO-VEHICLE',
    ]);

    // Verify RFID has null vehicle_number
    $rfid = \App\Models\Rfid::where('uid', 'RFID-NO-VEHICLE')->first();
    $this->assertNull($rfid->vehicle_number);
  }
}
