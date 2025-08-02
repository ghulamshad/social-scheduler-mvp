<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return response()->json([
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->delete();
        
        return response()->json([
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll(): JsonResponse
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)->delete();
        
        return response()->json([
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Send notification to user
     */
    public static function sendNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): Notification {
        return Notification::createNotification(
            $userId,
            $type,
            $title,
            $message,
            $category,
            $data
        );
    }

    /**
     * Send success notification
     */
    public static function sendSuccess(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): Notification {
        return Notification::createSuccess($userId, $title, $message, $category, $data);
    }

    /**
     * Send error notification
     */
    public static function sendError(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): Notification {
        return Notification::createError($userId, $title, $message, $category, $data);
    }

    /**
     * Send warning notification
     */
    public static function sendWarning(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): Notification {
        return Notification::createWarning($userId, $title, $message, $category, $data);
    }

    /**
     * Send info notification
     */
    public static function sendInfo(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): Notification {
        return Notification::createInfo($userId, $title, $message, $category, $data);
    }
} 