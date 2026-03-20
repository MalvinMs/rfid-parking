<?php

namespace Database\Seeders;

use App\Models\ParkingSession;
use Illuminate\Database\Seeder;

class ParkingSessionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create sample active sessions
    ParkingSession::create([
      'rfid_uid' => 'RFID-001',
      'vehicle_number' => 'ABC-1234',
      'check_in_at' => now()->subMinutes(30),
      'expires_at' => now()->addMinutes(30),
      'check_out_at' => null,
    ]);

    ParkingSession::create([
      'rfid_uid' => 'RFID-002',
      'vehicle_number' => 'XYZ-5678',
      'check_in_at' => now()->subMinutes(5),
      'expires_at' => now()->addMinutes(55),
      'check_out_at' => null,
    ]);

    // Create overtime session
    ParkingSession::create([
      'rfid_uid' => 'RFID-003',
      'vehicle_number' => 'LMN-9012',
      'check_in_at' => now()->subHours(2),
      'expires_at' => now()->subMinutes(30),
      'check_out_at' => null,
    ]);

    // Create checked-out session
    ParkingSession::create([
      'rfid_uid' => 'RFID-004',
      'vehicle_number' => 'DEF-3456',
      'check_in_at' => now()->subHours(3),
      'expires_at' => now()->subHours(2),
      'check_out_at' => now()->subHours(1),
    ]);

    // Create another checked-out session
    ParkingSession::create([
      'rfid_uid' => 'RFID-005',
      'vehicle_number' => 'GHI-7890',
      'check_in_at' => now()->subHours(4),
      'expires_at' => now()->subHours(3),
      'check_out_at' => now()->subHours(2)->addMinutes(30),
    ]);
  }
}
