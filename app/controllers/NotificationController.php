<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
    }

    public function getUnread(): void
    {
        $notifications = $this->notificationModel->getUnread(current_user_id());
        $count = $this->notificationModel->getUnreadCount(current_user_id());

        $this->json([
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }

    public function markRead(string $id): void
    {
        $this->notificationModel->markAsRead((int) $id, current_user_id());
        $this->json(['success' => true]);
    }

    public function markAllRead(): void
    {
        $this->notificationModel->markAllAsRead(current_user_id());
        $this->json(['success' => true]);
    }
}
