<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    public function poll(): JsonResponse
    {
        $admin = Auth::user();

        if (! $admin instanceof User) {
            return response()->json([
                'count' => 0,
                'items' => [],
            ]);
        }

        $notifications = $admin->unreadNotifications()->latest()->limit(5)->get();

        return response()->json([
            'count' => $notifications->count(),
            'items' => $notifications->map(fn ($notification) => [
                'title' => data_get($notification->data, 'title', 'Notification'),
                'message' => data_get($notification->data, 'message', ''),
                'url' => data_get($notification->data, 'url', '#'),
            ])->values()->all(),
        ]);
    }
}
