<?php

namespace App\Events;

use App\Models\Notification;
use Pusher\Pusher;

class NewNotification
{
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcast()
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        ];

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        // 🔥 Channel riêng cho từng user
        // Ví dụ: user-12, user-5 ...
        $channel = 'user-' . $this->notification->user_id;

        // 🔥 Event tên "new-notification"
        $pusher->trigger($channel, 'new-notification', [
            'notification' => $this->notification
        ]);
    }
}
