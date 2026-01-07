<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyController;

Route::get('/', function () {
    return view('home');
})->name('home');


// -------------------- AUTH --------------------
// Register routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

// Logout route (only accessible for logged-in users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email verification
Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');
