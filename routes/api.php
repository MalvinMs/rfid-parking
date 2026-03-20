<?php

use App\Http\Controllers\Api\RfidController;
use Illuminate\Support\Facades\Route;

Route::prefix('rfid')->group(function () {
  // Hardware RFID scan endpoint
  // POST /api/rfid/scan
  // Body: { "uid": "RFID_UID" }
  // Header: x-api-key: YOUR_SECRET_KEY
  Route::post('/scan', [RfidController::class, 'scan']);
});
