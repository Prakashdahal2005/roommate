<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureProfileExists;
use Illuminate\Support\Facades\Route;

//homepage route
Route::get('/',[ProfileController::class,'index'])->name('home');

//auth routes
Route::get('/register',[AuthController::class,'showRegister'])->name('register');
Route::post('/register',[AuthController::class,'register'])->name('register.submit');
Route::get('/login',[AuthController::class,'showLogin'])->name('login');
Route::post('/login',[AuthController::class,'login'])->name('login.submit');
Route::post('/logout',[AuthController::class,'logout'])->name('logout');

//profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

 
//matching profiles
// web.php
Route::get('/profiles/matches', [ProfileController::class, 'matches'])->name('profiles.matches');
