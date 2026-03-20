<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rfid_id', 'rfid_uid', 'check_in_at', 'expires_at', 'check_out_at'])]
class ParkingSession extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'check_in_at' => 'datetime',
      'expires_at' => 'datetime',
      'check_out_at' => 'datetime',
    ];
  }

  /**
   * Get the RFID associated with this session.
   */
  public function rfid(): BelongsTo
  {
    return $this->belongsTo(Rfid::class);
  }

  /**
   * Get the current status of the parking session.
   *
   * @return string One of: 'OUT', 'OVERTIME', 'NORMAL'
   */
  public function getStatusAttribute(): string
  {
    // If checked out, status is OUT
    if ($this->check_out_at !== null) {
      return 'OUT';
    }

    // If current time has exceeded expiry time, status is OVERTIME
    if (now() > $this->expires_at) {
      return 'OVERTIME';
    }

    // Otherwise, status is NORMAL
    return 'NORMAL';
  }

  /**
   * Get the remaining minutes until expiry.
   * Negative values indicate overtime minutes.
   *
   * @return int Minutes remaining (can be negative for overtime)
   */
  public function getRemainingMinutesAttribute(): int
  {
    // If checked out, no remaining time
    if ($this->check_out_at !== null) {
      return 0;
    }

    $now = now();
    $remaining = $now->diffInMinutes($this->expires_at, false);

    return $remaining;
  }

  /**
   * Get the formatted remaining time display.
   * Shows negative values as overtime hours/minutes.
   *
   * @return string Human-readable remaining time
   */
  public function getFormattedRemainingTimeAttribute(): string
  {
    $minutes = $this->remaining_minutes;

    if ($minutes >= 0) {
      $hours = intdiv($minutes, 60);
      $mins = $minutes % 60;
      return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
    }

    // For overtime (negative values)
    $overtimeMinutes = abs($minutes);
    $hours = intdiv($overtimeMinutes, 60);
    $mins = $overtimeMinutes % 60;
    return $hours > 0 ? "+{$hours}h {$mins}m" : "+{$mins}m";
  }

  /**
   * Get all active parking sessions (not checked out).
   */
  public static function active()
  {
    return static::whereNull('check_out_at');
  }

  /**
   * Get all overtime sessions (not checked out and expired).
   */
  public static function overtime()
  {
    return static::whereNull('check_out_at')
      ->where('expires_at', '<', now());
  }

  /**
   * Get all checked out sessions.
   */
  public static function checkedOut()
  {
    return static::whereNotNull('check_out_at');
  }
}
