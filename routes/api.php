<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Resources\UserResource;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::get('sanctum/csrf-cookie', function (Request $request) {
    // CSRFトークンをセットしたレスポンスを返します
    return response()->json(['message' => 'CSRF token set']);
});

// ログイン
Route::post('/login', [AuthController::class, 'login']);

// ログアウト
Route::post('/logout', [AuthController::class, 'logout']);

// 認証必要
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('projects', ProjectController::class);
});
