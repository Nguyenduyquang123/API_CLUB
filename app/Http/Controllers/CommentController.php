<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Events\CommentCreated;
use App\Events\NewNotification;
use App\Models\ClubMember;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Pusher\Pusher;
class CommentController extends Controller
{
    // Lấy tất cả comment theo post_id
    public function index($postId)
    {
        $comments = PostComment::where('post_id', $postId)
            ->with('user','likes')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    // Tạo comment mới
    public function store(Request $request, $postId)
{
    // Validate dữ liệu
    $this->validate($request, [
        'user_id' => 'required|exists:users,id',
        'content' => 'required|string|max:1000',
    ]);

    // Tìm bài viết
    $post = Post::find($postId);
    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    // Tạo comment
    $comment = PostComment::create([
        'post_id' => $postId,
        'user_id' => $request->user_id,
        'content' => $request->content,
    ]);

    // Tạo notification chỉ khi người comment **khác chủ post**
    if ($request->user_id != $post->user_id) {
        $notification = Notification::create([
            'user_id' => $post->user_id,                 // người nhận thông báo
            'from_user_id' => $request->user_id,        // người gửi (người comment)
            'type' => 'comment',
            'club_id' => $post->club_id,                // nếu post thuộc club
            'title' => 'Có bình luận mới',
            'message' => 'đã bình luận bài đăng: "' . $post->title . '"',
            'related_post_id' => $post->id,
            'related_comment_id' => $comment->id,
            'is_read' => false
        ]);

        // Broadcast notification
        (new NewNotification($notification))->broadcast();
    }

    // Broadcast comment cho realtime
    (new CommentCreated($comment))->broadcast();

    return response()->json($comment->load('user'), 201);
}

public function delete($id, Request $request)
{
    $userId = $request->user_id;

    // Lấy comment + post
    $comment = PostComment::with('post')->find($id);
    if (!$comment) {
        return response()->json(['message' => 'Không tìm thấy bình luận'], 404);
    }

    // Lấy user
    $user = User::find($userId);
    if (!$user) {
        return response()->json(['message' => 'User không tồn tại'], 404);
    }

    // Lấy club_id từ bài post
    $clubId = $comment->post->club_id ?? null;

    if (!$clubId) {
        return response()->json(['message' => 'Không xác định được club'], 400);
    }

    // ⭐ LẤY ROLE TRONG CLUB
    $member = ClubMember::where('club_id', $clubId)
                        ->where('user_id', $userId)
                        ->first();

    $clubRole = $member->role ?? null; // owner, admin, member,...

    // Kiểm tra quyền
    $isCommentOwner = $comment->user_id == $userId;
    $isPostOwner = $comment->post->creator_id == $userId;

    // Cho phép nếu:
    // - chính người viết comment
    // - hoặc là owner của club
    // - hoặc là admin của club
    if (
        !$isCommentOwner &&
        !in_array($clubRole, ['owner', 'admin']) &&
        !$isPostOwner
    ) {
        return response()->json(['message' => 'Bạn không có quyền xoá bình luận'], 403);
    }

    // Xóa comment
    $comment->delete();

    return response()->json(['message' => 'Đã xoá bình luận']);
}




}

