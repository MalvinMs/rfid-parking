<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Create RFID and Parking Sessions schema for production
   * Combines all parking management tables into one migration
   */
  public function up(): void
  {
    // Create RFID devices table
    Schema::create('rfids', function (Blueprint $table) {
      $table->id();
      $table->string('uid')->unique();
      $table->string('name')->nullable();
      $table->string('owner_name')->nullable();
      $table->string('vehicle_number')->nullable();
      $table->string('phone')->nullable();
      $table->text('notes')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->index('uid');
      $table->index('is_active');
    });

    // Create parking sessions table
    Schema::create('parking_sessions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('rfid_id')->nullable()->constrained('rfids')->onDelete('set null');
      $table->string('rfid_uid');
      $table->timestamp('check_in_at');
      $table->timestamp('expires_at');
      $table->timestamp('check_out_at')->nullable();
      $table->timestamps();

      $table->index('check_out_at');
      $table->index('expires_at');
      $table->index('rfid_uid');

      // Unique constraint: only one active session per RFID
      // (allows same RFID after checkout)
      $table->unique(['rfid_id', 'check_out_at'], 'unique_active_rfid');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('parking_sessions');
    Schema::dropIfExists('rfids');
  }
};
