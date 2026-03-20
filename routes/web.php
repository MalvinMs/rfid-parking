<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParkingSessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect root to parking dashboard
Route::redirect('/', '/parking');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', function () {
  Auth::logout();
  session()->invalidate();
  session()->regenerateToken();
  return redirect('/parking')->with('success', 'Logged out successfully');
})->name('logout')->middleware('auth');

// Parking Management Routes
Route::prefix('parking')->name('parking.')->group(function () {
  Route::get('/', [ParkingSessionController::class, 'dashboard'])->name('dashboard');
  Route::get('/check-in', [ParkingSessionController::class, 'checkInForm'])->name('check-in-form');
  Route::post('/check-in', [ParkingSessionController::class, 'checkIn'])->name('check-in');
  Route::post('/{session}/check-out', [ParkingSessionController::class, 'checkOut'])->name('check-out');
  Route::get('/history', [ParkingSessionController::class, 'history'])->name('history');
});
