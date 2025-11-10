<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Events\CommentCreated;
use Pusher\Pusher;
class CommentController extends Controller
{
    // Lấy tất cả comment theo post_id
    public function index($postId)
    {
        $comments = PostComment::where('post_id', $postId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    // Tạo comment mới
    public function store(Request $request, $postId)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = PostComment::create([
            'post_id' => $postId,
            'user_id' => $request->user_id,
            'content' => $request->content,
        ]);
        (new CommentCreated($comment))->broadcast();
        return response()->json($comment->load('user'), 201);
    }
}

