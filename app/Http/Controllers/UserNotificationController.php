<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserNotificationResource;
use App\Models\UserNotification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();

        $notifications = UserNotification::with('task')
            ->where('user_id',  $user->id)
            ->latest()
            ->get();

        return UserNotificationResource::collection($notifications);
    }

    public function markAsRead(UserNotification $notification): Response
    {
        $user = Auth::user();

        abort_unless($notification->user_id === $user->id, 403);

        $notification->update([
            'read_at' => now(),
        ]);

        return response()->noContent();
    }
}