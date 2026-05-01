<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // ユーザーが存在するかを確認
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // ユーザーが見つからない場合
                Log::error('User not found', ['email' => $request->email]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // パスワード照合
            if (!Hash::check($request->password, $user->password)) {
                // パスワードが一致しない場合
                Log::error('Invalid password', ['email' => $request->email]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // ユーザーをログインさせ、セッションを再生成
            Auth::login($user);
            $request->session()->regenerate(); // セッションの再生成

            // ログイン成功
            Log::info('User logged in', ['user_id' => $user->id]);

            // ユーザー情報を返す
            return response()->json([
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            // エラーログを記録
            Log::error('Login Error: ' . $e->getMessage());

            // エラー応答
            return response()->json(['message' => 'Internal server error.'], 500);
        }
    }
}
