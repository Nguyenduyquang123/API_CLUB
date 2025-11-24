<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function index($postId)
    {
        $likeCount = PostLike::where('post_id', $postId)->count();
        return response()->json(['like_count' => $likeCount]);
    }

public function toggleLike(Request $request)
{
    $postId = $request->input('post_id');
    $userId = $request->input('user_id');

    if (!$postId || !$userId) {
        return response()->json(['error' => 'Thiếu post_id hoặc user_id'], 400);
    }

    $existing = PostLike::where('post_id', $postId)
        ->where('user_id', $userId)
        ->first();

    // Lấy thông tin bài viết để biết chủ bài viết
    $post = Post::find($postId);

    if (!$post) {
        return response()->json(['error' => 'Bài viết không tồn tại'], 404);
    }

    if ($existing) {
        // Nếu đã like → bỏ like
        $existing->delete();
        $liked = false;

    } else {
        // Nếu chưa like → thêm like
        PostLike::create([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);

        $liked = true;

        // 🔥 Chỉ tạo notification nếu like bài của người khác
        if ($userId != $post->user_id) {

            $noti = Notification::create([
                'user_id'          => $post->user_id,  // người nhận thông báo
                'from_user_id'     => $userId,         // người tạo thông báo
                'type'             => 'like',
                'title'            => 'đã thích bài viết của bạn.',
                'related_post_id'  => $postId,
                'is_read'          => 0,
            ]);

             (new NewNotification($noti))->broadcast();
        }
    }

    $likeCount = PostLike::where('post_id', $postId)->count();

    return response()->json([
        'liked' => $liked,
        'like_count' => $likeCount
    ]);
}

    public function checkLike(Request $request)
    {
        $postId = $request->input('post_id');
        $userId = $request->input('user_id');

        $liked = PostLike::where('post_id', $postId)
            ->where('user_id', $userId)
            ->exists();

        return response()->json(['liked' => $liked]);
    }
}
