<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PushNotificationService
{
    /**
     * Send push notification to user.
     */
    public function sendToUser($userId, $title, $body, $data = []): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::warning("Push notification failed: User not found", ['user_id' => $userId]);
                return false;
            }

            // Get user's mobile sessions
            $mobileSessions = UserSession::where('user_id', $userId)
                ->where('is_active', true)
                ->where('is_mobile', true)
                ->where('expires_at', '>', Carbon::now())
                ->get();

            if ($mobileSessions->isEmpty()) {
                Log::info("No active mobile sessions for user", ['user_id' => $userId]);
                return false;
            }

            $successCount = 0;
            foreach ($mobileSessions as $session) {
                if ($this->sendToDevice($session->device_id, $title, $body, $data)) {
                    $successCount++;
                }
            }

            // Log notification
            $this->logNotification($userId, $title, $body, $data, $successCount, $mobileSessions->count());

            return $successCount > 0;

        } catch (\Exception $e) {
            Log::error("Push notification error", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send push notification to specific device.
     */
    public function sendToDevice($deviceId, $title, $body, $data = []): bool
    {
        try {
            // Get device session
            $session = UserSession::where('device_id', $deviceId)
                ->where('is_active', true)
                ->where('is_mobile', true)
                ->first();

            if (!$session) {
                Log::warning("Device session not found", ['device_id' => $deviceId]);
                return false;
            }

            // Prepare notification payload
            $payload = [
                'title' => $title,
                'body' => $body,
                'data' => array_merge($data, [
                    'timestamp' => Carbon::now()->toISOString(),
                    'notification_id' => uniqid('notif_'),
                ]),
                'device_type' => $session->device_type,
                'user_id' => $session->user_id,
            ];

            // Send based on device type
            switch ($session->device_type) {
                case 'ios':
                    return $this->sendToIOS($deviceId, $payload);
                case 'android':
                    return $this->sendToAndroid($deviceId, $payload);
                default:
                    Log::warning("Unsupported device type", ['device_type' => $session->device_type]);
                    return false;
            }

        } catch (\Exception $e) {
            Log::error("Device notification error", [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to iOS device.
     */
    private function sendToIOS($deviceId, $payload): bool
    {
        try {
            // This would integrate with Apple Push Notification Service (APNs)
            // For now, we'll simulate the notification
            
            Log::info("iOS notification sent", [
                'device_id' => $deviceId,
                'payload' => $payload,
            ]);

            // Store notification in cache for tracking
            $this->storeNotification($deviceId, $payload);

            return true;

        } catch (\Exception $e) {
            Log::error("iOS notification error", [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to Android device.
     */
    private function sendToAndroid($deviceId, $payload): bool
    {
        try {
            // This would integrate with Firebase Cloud Messaging (FCM)
            // For now, we'll simulate the notification
            
            Log::info("Android notification sent", [
                'device_id' => $deviceId,
                'payload' => $payload,
            ]);

            // Store notification in cache for tracking
            $this->storeNotification($deviceId, $payload);

            return true;

        } catch (\Exception $e) {
            Log::error("Android notification error", [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers($userIds, $title, $body, $data = []): array
    {
        $results = [];
        
        foreach ($userIds as $userId) {
            $results[$userId] = $this->sendToUser($userId, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Send notification to all active mobile users.
     */
    public function sendToAllUsers($title, $body, $data = []): array
    {
        $activeUsers = UserSession::where('is_active', true)
            ->where('is_mobile', true)
            ->where('expires_at', '>', Carbon::now())
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        return $this->sendToUsers($activeUsers, $title, $body, $data);
    }

    /**
     * Send file-related notification.
     */
    public function sendFileNotification($userId, $action, $fileName, $additionalData = []): bool
    {
        $notifications = [
            'upload' => [
                'title' => 'File Uploaded',
                'body' => "File '{$fileName}' has been uploaded successfully",
            ],
            'download' => [
                'title' => 'File Downloaded',
                'body' => "File '{$fileName}' has been downloaded",
            ],
            'shared' => [
                'title' => 'File Shared',
                'body' => "File '{$fileName}' has been shared with you",
            ],
            'updated' => [
                'title' => 'File Updated',
                'body' => "File '{$fileName}' has been updated",
            ],
            'deleted' => [
                'title' => 'File Deleted',
                'body' => "File '{$fileName}' has been deleted",
            ],
        ];

        if (!isset($notifications[$action])) {
            Log::warning("Unknown file action for notification", ['action' => $action]);
            return false;
        }

        $notification = $notifications[$action];
        $data = array_merge($additionalData, [
            'type' => 'file',
            'action' => $action,
            'file_name' => $fileName,
        ]);

        return $this->sendToUser($userId, $notification['title'], $notification['body'], $data);
    }

    /**
     * Send security notification.
     */
    public function sendSecurityNotification($userId, $type, $message, $additionalData = []): bool
    {
        $notifications = [
            'login' => [
                'title' => 'New Login',
                'body' => 'New login detected on your account',
            ],
            'logout' => [
                'title' => 'Logout',
                'body' => 'You have been logged out',
            ],
            'password_change' => [
                'title' => 'Password Changed',
                'body' => 'Your password has been changed',
            ],
            'security_alert' => [
                'title' => 'Security Alert',
                'body' => $message,
            ],
            'suspicious_activity' => [
                'title' => 'Suspicious Activity',
                'body' => 'Suspicious activity detected on your account',
            ],
        ];

        if (!isset($notifications[$type])) {
            Log::warning("Unknown security notification type", ['type' => $type]);
            return false;
        }

        $notification = $notifications[$type];
        $data = array_merge($additionalData, [
            'type' => 'security',
            'security_type' => $type,
        ]);

        return $this->sendToUser($userId, $notification['title'], $notification['body'], $data);
    }

    /**
     * Send system notification.
     */
    public function sendSystemNotification($title, $body, $data = []): array
    {
        $data = array_merge($data, [
            'type' => 'system',
        ]);

        return $this->sendToAllUsers($title, $body, $data);
    }

    /**
     * Get user notification history.
     */
    public function getUserNotifications($userId, $limit = 50): array
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        return array_slice($notifications, 0, $limit);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($userId, $notificationId): bool
    {
        try {
            $cacheKey = "user_notifications_{$userId}";
            $notifications = Cache::get($cacheKey, []);

            foreach ($notifications as &$notification) {
                if ($notification['notification_id'] === $notificationId) {
                    $notification['read_at'] = Carbon::now()->toISOString();
                    break;
                }
            }

            Cache::put($cacheKey, $notifications, 60 * 24 * 7); // 7 days

            return true;

        } catch (\Exception $e) {
            Log::error("Error marking notification as read", [
                'user_id' => $userId,
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear user notifications.
     */
    public function clearUserNotifications($userId): bool
    {
        try {
            $cacheKey = "user_notifications_{$userId}";
            Cache::forget($cacheKey);

            return true;

        } catch (\Exception $e) {
            Log::error("Error clearing user notifications", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Store notification for tracking.
     */
    private function storeNotification($deviceId, $payload): void
    {
        try {
            $session = UserSession::where('device_id', $deviceId)->first();
            if (!$session) return;

            $cacheKey = "user_notifications_{$session->user_id}";
            $notifications = Cache::get($cacheKey, []);

            $notification = [
                'notification_id' => $payload['data']['notification_id'],
                'title' => $payload['title'],
                'body' => $payload['body'],
                'data' => $payload['data'],
                'device_id' => $deviceId,
                'sent_at' => Carbon::now()->toISOString(),
                'read_at' => null,
            ];

            array_unshift($notifications, $notification);

            // Keep only last 100 notifications
            $notifications = array_slice($notifications, 0, 100);

            Cache::put($cacheKey, $notifications, 60 * 24 * 7); // 7 days

        } catch (\Exception $e) {
            Log::error("Error storing notification", [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log notification for analytics.
     */
    private function logNotification($userId, $title, $body, $data, $successCount, $totalCount): void
    {
        try {
            // This would typically log to a notifications table
            Log::info("Notification sent", [
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'success_count' => $successCount,
                'total_count' => $totalCount,
                'success_rate' => $totalCount > 0 ? ($successCount / $totalCount) * 100 : 0,
            ]);

        } catch (\Exception $e) {
            Log::error("Error logging notification", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get notification statistics.
     */
    public function getNotificationStats($userId = null): array
    {
        $stats = [
            'total_sent' => 0,
            'total_delivered' => 0,
            'total_read' => 0,
            'delivery_rate' => 0,
            'read_rate' => 0,
        ];

        if ($userId) {
            $notifications = $this->getUserNotifications($userId, 1000);
            
            $stats['total_sent'] = count($notifications);
            $stats['total_delivered'] = count($notifications); // Assuming all sent are delivered
            $stats['total_read'] = count(array_filter($notifications, fn($n) => isset($n['read_at'])));
            
            if ($stats['total_sent'] > 0) {
                $stats['delivery_rate'] = ($stats['total_delivered'] / $stats['total_sent']) * 100;
                $stats['read_rate'] = ($stats['total_read'] / $stats['total_sent']) * 100;
            }
        }

        return $stats;
    }
}
