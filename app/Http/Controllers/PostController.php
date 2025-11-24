<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use Carbon\Carbon;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::with(['creator', 'club' ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($posts);
    }


    public function show($id)
    {
        $post = Post::with(['creator', 'club',])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post);
    }


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
        if ((int)$post->notify_members === 1) {
            $members = ClubMember::where('club_id', $post->club_id)
                                ->where('user_id', '!=', $post->user_id)
                                ->get();

            foreach ($members as $member) {
                $noti = Notification::create([
                    'user_id' => $member->user_id,
                    'type' => 'club_post',
                    'club_id' => $post->club_id,
                    'title' => 'Thông báo: ' . $post->title,
                    'message' => $post->content,
                    'related_post_id' => $post->id,
                    'related_comment_id' => null,
                    'is_read' => false,
                ]);

                // 🔥 Gửi realtime
                (new \App\Events\NewNotification($noti))->broadcast();
            }
        }

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }


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


public function destroy(Request $request, $id)
{
    $post = Post::find($id);

    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    $authUserId = $request->input('auth_user_id');

    if (!$authUserId) {
        return response()->json(['message' => 'Missing auth_user_id'], 400);
    }

  
    $clubMember = ClubMember::where('club_id', $post->club_id)
                            ->where('user_id', $authUserId)
                            ->first();

    if (!$clubMember) {
        return response()->json(['message' => 'User is not a member of this club'], 403);
    }

    $userRoleInClub = $clubMember->role;

   
    $postCreatorMember = ClubMember::where('club_id', $post->club_id)
                                    ->where('user_id', $post->user_id)
                                    ->first();
    $postCreatorRole = $postCreatorMember ? $postCreatorMember->role : null;

   
    if ($authUserId == $post->user_id) {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully (owner of post)']);
    }

   
    if ($userRoleInClub === 'owner') {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully (owner)']);
    }

  
    if ($userRoleInClub === 'admin' && $postCreatorRole === 'member') {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully (admin)']);
    }

    return response()->json(['message' => 'Bạn không có quyền xóa bài này'], 403);
}


    public function getByClub($club_id)
    {
        $club = Club::find($club_id);
        $members = Club::with('members')->find($club_id); // load members luôn
        if (!$club) {
            return response()->json(['message' => 'Không tìm thấy câu lạc bộ.'], 404);
        }
        

        $posts = Post::with(['creator','comments', 'likes'])
            ->withCount('likes')    
            ->where('club_id', $club_id)
            ->orderByDesc('is_pinned') // bài ghim lên đầu
            ->orderByDesc('created_at') // bài mới nhất trước
            ->get();

        return response()->json([
            'club' => $club->name,
            'total_posts' => $posts->count(),
            'posts' => $posts,
            'members' => $members->members
        ]);
    }


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
