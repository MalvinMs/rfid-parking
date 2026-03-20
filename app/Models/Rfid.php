<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfid extends Model
{
  protected $fillable = [
    'uid',
    'name',
    'owner_name',
    'vehicle_number',
    'phone',
    'notes',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Get all parking sessions for this RFID.
   */
  public function parkingSessions(): HasMany
  {
    return $this->hasMany(ParkingSession::class);
  }

  /**
   * Get active parking session (if any).
   */
  public function activeSession()
  {
    return $this->parkingSessions()
      ->whereNull('check_out_at')
      ->latest('check_in_at')
      ->first();
  }
}
