<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingSessionController extends Controller
{
  /**
   * Show the parking monitoring dashboard.
   */
  public function dashboard(): View
  {
    $sessions = ParkingSession::active()
      ->orderByDesc('check_in_at')
      ->get();

    return view('parking.dashboard', [
      'sessions' => $sessions,
    ]);
  }

  /**
   * Show the check-in form.
   */
  public function checkInForm(): View
  {
    return view('parking.check-in');
  }

  /**
   * Handle check-in submission (simulate RFID input).
   */
  public function checkIn(Request $request)
  {
    $validated = $request->validate([
      'rfid_uid' => 'required|string|unique:parking_sessions,rfid_uid,NULL,id,check_out_at,NULL',
      'vehicle_number' => 'nullable|string|max:100',
    ], [
      'rfid_uid.unique' => 'This RFID is already checked in. Please check out first.',
    ]);

    $now = now();

    $session = ParkingSession::create([
      'rfid_uid' => $validated['rfid_uid'],
      'vehicle_number' => $validated['vehicle_number'],
      'check_in_at' => $now,
      'expires_at' => $now->copy()->addMinutes(85),
    ]);

    return redirect()
      ->route('parking.dashboard')
      ->with('success', "Vehicle {$session->rfid_uid} checked in successfully!");
  }

  /**
   * Handle check-out submission.
   */
  public function checkOut(ParkingSession $session)
  {
    if ($session->check_out_at !== null) {
      return redirect()
        ->route('parking.dashboard')
        ->with('warning', 'This session is already checked out.');
    }

    $session->update([
      'check_out_at' => now(),
    ]);

    return redirect()
      ->route('parking.dashboard')
      ->with('success', "Vehicle {$session->rfid_uid} checked out successfully!");
  }

  /**
   * Show all parking sessions (history).
   */
  public function history(): View
  {
    $sessions = ParkingSession::orderByDesc('check_in_at')
      ->paginate(50);

    return view('parking.history', [
      'sessions' => $sessions,
    ]);
  }
}
