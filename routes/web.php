<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Menampilkan halaman login
Route::get('/login', [AuthController::class, 'index'])->name('login');

// Memproses data form login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');