<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Resources\UserResource;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskCommentController;

Route::get('sanctum/csrf-cookie', function (Request $request) {
    // CSRFトークンをセットしたレスポンスを返します
    return response()->json(['message' => 'CSRF token set']);
});

//ログイン
Route::post('/login', [AuthController::class, 'login']);

//ログアウト
Route::post('/logout', [AuthController::class, 'logout']);

// 認証必要
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('projects', ProjectController::class);

    // タスクステータス更新用
    Route::put('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    // タスクステータス選択用
    Route::get('/task-statuses', [TaskStatusController::class, 'index']);
    // タスクコメント
    Route::get('/tasks/{task}/comments', [TaskCommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store']);
    Route::put('/task-comments/{comment}', [TaskCommentController::class, 'update']);
    Route::delete('/task-comments/{comment}', [TaskCommentController::class, 'destroy']);
});
