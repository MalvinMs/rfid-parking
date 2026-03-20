<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSession;
use App\Models\Rfid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidController extends Controller
{
  /**
   * API Key for hardware device authentication
   */
  protected const API_KEY = 'rfid-esp32-secret-key-2026';

  /**
   * Handle RFID scan from hardware device (ESP32).
   *
   * Toggle logic:
   * - If no active session exists → CHECK-IN (create new session)
   * - If active session exists → CHECK-OUT (mark check_out_at)
   *
   * @param Request $request JSON body: { "uid": "RFID123" }
   * @return JsonResponse
   */
  public function scan(Request $request): JsonResponse
  {
    // Validate API key from header
    if (!$this->validateApiKey($request)) {
      return response()->json([
        'error' => 'Unauthorized',
        'message' => 'Invalid or missing API key',
      ], 403);
    }

    // Validate request body
    $validated = $request->validate([
      'uid' => 'required|string|max:255',
      'vehicle_number' => 'nullable|string|max:255',
    ]);

    $uid = $validated['uid'];
    $vehicleNumber = $validated['vehicle_number'] ?? null;

    // Find or create RFID device
    $rfid = Rfid::firstOrCreate(['uid' => $uid], [
      'name' => 'Device ' . strtoupper(substr($uid, -4)),
      'vehicle_number' => $vehicleNumber,
      'is_active' => true,
    ]);

    // Update vehicle_number if provided
    if ($vehicleNumber && !$rfid->wasRecentlyCreated) {
      $rfid->update(['vehicle_number' => $vehicleNumber]);
    }

    // Find active session for this RFID
    $activeSession = ParkingSession::where('rfid_id', $rfid->id)
      ->whereNull('check_out_at')
      ->first();

    // Toggle logic: check-in if no session, check-out if exists
    if ($activeSession === null) {
      // No active session → CHECK-IN
      return $this->handleCheckIn($rfid);
    } else {
      // Active session exists → CHECK-OUT
      return $this->handleCheckOut($activeSession);
    }
  }

  /**
   * Handle check-in: Create new parking session.
   *
   * @param Rfid $rfid RFID device
   * @return JsonResponse
   */
  private function handleCheckIn(Rfid $rfid): JsonResponse
  {
    $now = now();

    try {
      $session = ParkingSession::create([
        'rfid_id' => $rfid->id,
        'rfid_uid' => $rfid->uid,
        'check_in_at' => $now,
        'expires_at' => $now->copy()->addMinutes(85),
        'check_out_at' => null,
      ]);

      return response()->json([
        'action' => 'CHECK_IN',
        'status' => $session->status,
        'rfid_uid' => $session->rfid_uid,
        'vehicle_number' => $session->rfid->vehicle_number,
        'check_in_at' => $session->check_in_at->toIso8601String(),
        'expires_at' => $session->expires_at->toIso8601String(),
        'remaining_minutes' => $session->remaining_minutes,
      ], 201);
    } catch (\Exception $e) {
      // Handle duplicate RFID if somehow race condition occurs
      return response()->json([
        'error' => 'CHECK_IN_FAILED',
        'message' => 'Could not create parking session',
      ], 409);
    }
  }

  /**
   * Handle check-out: Mark session as checked out.
   *
   * @param ParkingSession $session
   * @return JsonResponse
   */
  private function handleCheckOut(ParkingSession $session): JsonResponse
  {
    $session->update([
      'check_out_at' => now(),
    ]);

    // Refresh to get updated status
    $session->refresh();

    return response()->json([
      'action' => 'CHECK_OUT',
      'status' => $session->status,
      'rfid_uid' => $session->rfid_uid,
      'vehicle_number' => $session->rfid->vehicle_number,
      'check_in_at' => $session->check_in_at->toIso8601String(),
      'expires_at' => $session->expires_at->toIso8601String(),
      'check_out_at' => $session->check_out_at->toIso8601String(),
      'remaining_minutes' => $session->remaining_minutes,
    ], 200);
  }

  /**
   * Validate API key from request header.
   *
   * @param Request $request
   * @return bool
   */
  private function validateApiKey(Request $request): bool
  {
    $apiKey = $request->header('x-api-key');

    // Use environment variable if set, otherwise use default
    $validKey = env('RFID_API_KEY', self::API_KEY);

    return $apiKey === $validKey;
  }
}
