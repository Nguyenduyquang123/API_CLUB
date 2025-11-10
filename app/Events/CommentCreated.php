<?php

namespace App\Events;

use App\Models\PostComment;
use Pusher\Pusher;

class CommentCreated
{
    protected $comment;

    public function __construct(PostComment $comment)
    {
        $this->comment = $comment;
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

        // 🔥 Channel phải khớp với phía frontend
        $pusher->trigger('comments-post-' . $this->comment->post_id, 'new-comment', [
            'comment' => $this->comment->load('user'),
        ]);
    }
}
