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
        $user = Auth::guard('tenant')->user() ?? Auth::user();

        if (! $user || ! method_exists($user, 'unreadNotifications')) {
            return response()->json([
                'count' => 0,
                'items' => [],
            ]);
        }

        $notifications = $user->unreadNotifications()->limit(5)->get();

        return response()->json([
            'count' => $notifications->count(),
            'items' => $notifications->map(fn($notification) => [
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notification'),
                'message' => data_get($notification->data, 'message', ''),
                'url' => data_get($notification->data, 'url', '#'),
                'detail_url' => url('/notifications/' . $notification->id),
                'download_url' => data_get($notification->data, 'download_url'),
                'type' => data_get($notification->data, 'type'),
            ])->values()->all(),
        ]);
    }
}
