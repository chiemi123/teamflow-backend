<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

Route::middleware(['web'])->post('/login', [AuthController::class, 'login']);
Route::middleware(['web'])->post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate(); // セッション削除
    $request->session()->regenerateToken(); // CSRFトークンの再生成

    return response()->json([
        'message' => 'Logged out'
    ]);
});
