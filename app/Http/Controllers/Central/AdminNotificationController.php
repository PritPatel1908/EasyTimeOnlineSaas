<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'items' => $notifications->map(fn ($notification) => [
                'id'           => $notification->id,
                'title'        => data_get($notification->data, 'title', 'Notification'),
                'message'      => data_get($notification->data, 'message', ''),
                'url'          => data_get($notification->data, 'url', '#'),
                'detail_url'   => url('/notifications/' . $notification->id),
                'download_url' => data_get($notification->data, 'download_url'),
                'type'         => data_get($notification->data, 'type'),
            ])->values()->all(),
        ]);
    }

    /**
     * Mark one or more notifications as read.
     *
     * Accepts JSON body: { "ids": ["uuid1", "uuid2", ...] }
     * Called by the import-poll partial once it detects an import completion
     * notification so the same notification does not fire again on subsequent polls.
     */
    public function markRead(Request $request): JsonResponse
    {
        $user = Auth::guard('tenant')->user() ?? Auth::user();

        if (! $user || ! method_exists($user, 'notifications')) {
            return response()->json(['marked' => 0]);
        }

        $ids = $request->input('ids', []);

        if (empty($ids) || ! is_array($ids)) {
            return response()->json(['marked' => 0]);
        }

        $count = $user->unreadNotifications()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn ($n) => $n->markAsRead())
            ->count();

        return response()->json(['marked' => $count]);
    }
}
