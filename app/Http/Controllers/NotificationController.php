<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notification count for active user/seat
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0]);
        }

        $currentSeatUser = $user->currentSeatUser();
        $seatId = $currentSeatUser?->seat_id;

        $unreadCount = AppNotification::forRecipient($user, $seatId)
            ->unread()
            ->count();

        $latestId = AppNotification::forRecipient($user, $seatId)
            ->latest('id')
            ->value('id');

        return response()->json([
            'unread_count' => $unreadCount,
            'latest_id' => $latestId,
        ]);
    }

    /**
     * Get latest notifications list (for dropdown)
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        $currentSeatUser = $user->currentSeatUser();
        $seatId = $currentSeatUser?->seat_id;

        $filter = $request->query('filter', 'all'); // 'all' or 'unread'

        $query = AppNotification::forRecipient($user, $seatId);

        if ($filter === 'unread') {
            $query->unread();
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->take(25)
            ->get()
            ->map(function (AppNotification $n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'link' => $n->link,
                    'icon' => $n->icon ?: 'bell',
                    'color' => $n->color ?: 'indigo',
                    'is_read' => !is_null($n->read_at),
                    'time_ago' => $n->created_at ? $n->created_at->diffForHumans() : '',
                    'date' => $n->created_at ? $n->created_at->format('d M, h:i A') : '',
                ];
            });

        $unreadCount = AppNotification::forRecipient($user, $seatId)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $currentSeatUser = $user->currentSeatUser();
        $seatId = $currentSeatUser?->seat_id;

        $notification = AppNotification::forRecipient($user, $seatId)->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for current user/seat
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $currentSeatUser = $user->currentSeatUser();
        $seatId = $currentSeatUser?->seat_id;

        AppNotification::forRecipient($user, $seatId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $currentSeatUser = $user->currentSeatUser();
        $seatId = $currentSeatUser?->seat_id;

        $notification = AppNotification::forRecipient($user, $seatId)->find($id);

        if ($notification) {
            $notification->delete();
        }

        return response()->json(['success' => true]);
    }
}

