<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use Illuminate\Http\Request;
use App\Models\CommentLike;
use App\Models\Notification;
use App\Models\PostComment;

class CommentLikeController extends Controller
{
    public function index($commentId)
    {
        $count = CommentLike::where('comment_id', $commentId)->count();
        return response()->json(['like_count' => $count]);
    }

    public function toggleLike(Request $request)
{
    $commentId = $request->input('comment_id');
    $userId = $request->input('user_id');

    if (!$commentId || !$userId) {
        return response()->json(['error' => 'Thiếu comment_id hoặc user_id'], 400);
    }

   $comment = PostComment::with(['user', 'post'])->find($commentId);
if (!$comment) {
    return response()->json(['error' => 'Comment không tồn tại'], 404);
}
$clubId = $comment->post->club_id ?? null;

    $existing = CommentLike::where('comment_id', $commentId)
        ->where('user_id', $userId)
        ->first();

    if ($existing) {
        // Unlike
        $existing->delete();
        $liked = false;
         $oldNoti = Notification::where('from_user_id', $userId)
                ->where('user_id', $comment->user_id)
                ->where('type', 'comment_like')
                ->where('related_comment_id', $commentId)
                ->first();

    if ($oldNoti) {
        $oldNoti->delete();
    }

    } else {
        // Like
        CommentLike::create([
            'comment_id' => $commentId,
            'user_id' => $userId,
        ]);

        $liked = true;

      
       if ($userId != $comment->user_id) {
            $noti = Notification::create([
            'user_id' => $comment->user_id,     
            'from_user_id' => $userId,             
            'type' => 'comment_like',
            'club_id' => $clubId,
            'title' => 'Bình luận được thích',
            'message' => 'đã thích bình luận của bạn',
            'related_post_id' => $comment->post_id,
            'related_comment_id' => $commentId,
            'is_read' => false
        ]);

            
            (new NewNotification($noti))->broadcast();
        }
    }

    return response()->json([
        'liked' => $liked
    ]);
}


    public function checkLike(Request $request)
    {
        $commentId = $request->input('comment_id');
        $userId = $request->input('user_id');

        $liked = CommentLike::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->exists();

        return response()->json(['liked' => $liked]);
    }
}
