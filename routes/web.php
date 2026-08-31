<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



// ログインページの表示
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// ログイン処理
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// ログアウト処理
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
