<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    
    Route::get('/otp-login', [AuthController::class, 'showOtpLogin'])->name('auth.otp.login');
    Route::post('/send-otp', [AuthController::class, 'sendOtp'])->name('auth.otp.send')->middleware('throttle:otp');
    
    Route::get('/otp-verification', [AuthController::class, 'showOtpVerification'])->name('auth.otp.verification');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify')->middleware('throttle:otp');
    
    // Social login routes
    Route::get('/social/{provider}/redirect', [AuthController::class, 'socialRedirect'])->name('social.redirect');
    Route::get('/social/{provider}/callback', [AuthController::class, 'socialCallback'])->name('social.callback');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Default dashboard route
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Admin routes
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });
    
    // Customer routes
    Route::middleware('role:Customer')->prefix('customer')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'customer'])->name('customer.dashboard');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
