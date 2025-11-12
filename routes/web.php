<?php

use App\Http\Controllers\Admin\ClusterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureProfileExists;
use Illuminate\Support\Facades\Route;

//homepage route
Route::get('/', [HomepageController::class, 'index'])->name('home');

// simple about page
Route::view('/about', 'about')->name('about');

// simple contact page
Route::view('/contact', 'contact')->name('contact');

// guest-only auth routes (register, login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// auth-only routes (logout, profile, etc.)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

//guest can view profile
Route::get('/profile/{profile}', [ProfileController::class, 'show'])->name('profile.show');


// batch update for kmeans++ clusters ran by admin regularly (needs to be automated)
Route::get('/runkmean/{k}', [ClusterController::class, 'kMeanBatchUpdate']);
