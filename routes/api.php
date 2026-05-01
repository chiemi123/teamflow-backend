<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Resources\UserResource;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Sanctionによるセッション管理のため、`api`グループ内でミドルウェアを設定
Route::middleware(['api', EnsureFrontendRequestsAreStateful::class])->post('/login', [AuthController::class, 'login']);
Route::middleware(['api', EnsureFrontendRequestsAreStateful::class])->post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate(); // セッション削除
    $request->session()->regenerateToken(); // CSRFトークンの再生成

    return response()->json([
        'message' => 'Logged out'
    ]);
});


// ユーザー情報取得 (API認証が必要)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());  // 認証されたユーザー情報を返す
});

// APIリソース（Sanctum認証）
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('projects', ProjectController::class);
});
