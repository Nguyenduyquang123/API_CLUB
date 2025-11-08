<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * 🧩 Lấy danh sách tất cả bài viết
     */
    public function index()
    {
        $posts = Post::with(['creator', 'club'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($posts);
    }

    /**
     * 🧩 Lấy chi tiết 1 bài viết theo ID
     */
    public function show($id)
    {
        $post = Post::with(['creator', 'club'])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post);
    }

    /**
     * 🧩 Tạo bài viết mới
     */
    public function store(Request $request)
    {
        // ✅ Dùng validator thủ công vì Lumen không có $request->validate()
        $validated = $this->validateRequest($request, [
            'club_id' => 'required|integer',
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_pinned' => 'boolean',
            'notify_members' => 'boolean',
        ]);

        // 🧠 Tạo bài viết mới
        $post = Post::create([
            'club_id' => $validated['club_id'],
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $validated['is_pinned'] ?? false,
            'notify_members' => $validated['notify_members'] ?? false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    /**
     * 🧩 Cập nhật bài viết
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validated = $this->validateRequest($request, [
            'title' => 'string|max:255',
            'content' => 'string',
            'is_pinned' => 'boolean',
            'notify_members' => 'boolean',
        ]);

        $post->update($validated);

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post
        ]);
    }

    /**
     * 🧩 Xóa bài viết
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * 🧩 Lấy danh sách bài viết theo CLB
     */
 // 📌 Lấy bài đăng theo từng CLB
    public function getByClub($club_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['message' => 'Không tìm thấy câu lạc bộ.'], 404);
        }

        $posts = Post::with(['creator','comments'])
            ->where('club_id', $club_id)
            ->orderByDesc('is_pinned') // bài ghim lên đầu
            ->orderByDesc('created_at') // bài mới nhất trước
            ->get();

        return response()->json([
            'club' => $club->name,
            'total_posts' => $posts->count(),
            'posts' => $posts
        ]);
    }

    /**
     * 🧩 Hàm validate thủ công cho Lumen
     */
    private function validateRequest(Request $request, array $rules)
    {
        $validator = app('validator')->make($request->all(), $rules);

        if ($validator->fails()) {
            response()->json(['errors' => $validator->errors()], 422)->send();
            exit;
        }

        return $validator->validated();
    }
   
}
