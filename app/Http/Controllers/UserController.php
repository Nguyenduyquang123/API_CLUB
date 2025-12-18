<?php

namespace App\Http\Controllers;

use App\Models\EventParticipant;
use App\Models\Notification;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    public function index()
    {
        $users = DB::table('users')->get();
        return response()->json($users);
    }

    // 🟢 Lấy 1 user theo id
    public function show($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // 🟢 Tạo mới user
    public function store(Request $request)
    {
        $id = DB::table('users')->insertGetId([
            'username' => $request->username,
            'hashedPassword' => password_hash($request->password, PASSWORD_DEFAULT),
            'email' => $request->email,
            'displayName' => $request->displayName,
            'avatarUrl' => $request->avatarUrl,
            'avtarId' => $request->avtarId,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'User created successfully']);
    }

    // 🟢 Cập nhật user
    public function update(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // 🟢 1. Nếu có mật khẩu cũ + mật khẩu mới => đổi mật khẩu
        if ($request->filled('oldPassword') && $request->filled('password')) {
            if (!Hash::check($request->oldPassword, $user->hashedPassword)) {
                return response()->json(['message' => 'Mật khẩu cũ không đúng'], 400);
            }

            DB::table('users')->where('id', $id)->update([
                'hashedPassword' => Hash::make($request->password),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Đổi mật khẩu thành công'], 200);
        }

        // 🟢 2. Nếu chỉ muốn cập nhật thông tin cá nhân
        $data = [];
        if ($request->filled('displayName')) {
            $data['displayName'] = $request->displayName;
        }
        if ($request->filled('bio')) {
            $data['bio'] = $request->bio;
        }
        if ($request->filled('phone')) {
            $data['phone'] = $request->phone;
        }

        // Nếu không có gì để cập nhật
        if (empty($data)) {
            return response()->json(['message' => 'Không có dữ liệu để cập nhật'], 400);
        }

        $data['updated_at'] = Carbon::now();

        DB::table('users')->where('id', $id)->update($data);

        $updatedUser = DB::table('users')->where('id', $id)->first();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'user' => $updatedUser,
        ], 200);
    }

    // 🟢 Xóa user
    public function destroy($id)
    {
        $deleted = DB::table('users')->where('id', $id)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'User deleted successfully']);
    }
    public function myProfile(Request $request)
        {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Token không hợp lệ hoặc hết hạn'], 401);
            }

            return response()->json($user);
        }

    public function uploadAvatar(Request $request)
    {
        $user = $request->user(); // sẽ trả về user đúng

        if (!$request->hasFile('avatar')) {
            return response()->json(['message' => 'Không có ảnh tải lên'], 400);
        }

        $file = $request->file('avatar');
       $path = $file->store('avatars', 'public'); // lưu trong storage/app/public/avatars
        $baseUrl = $request->getSchemeAndHttpHost(); // http://localhost:8000
        $avatarUrl = $baseUrl . '/storage/' . $path;

        $user->avatarUrl = $avatarUrl;
        $user->save();

        return response()->json(['avatar' => $avatarUrl]);
            }
    public function find(Request $request)
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return response()->json(['message' => 'Thiếu tham số tìm kiếm'], 400);
        }

        $user = DB::table('users')
            ->where('email', $keyword)
            ->orWhere('username', $keyword)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'displayName' => $user->displayName,
            'avatarUrl' => $user->avatarUrl,
        ]);
    }
    public function getUserStats($userId)
    {
        // 🔹 Đếm tổng số bài viết do user tạo
        $postsCount = DB::table('posts')
            ->where('user_id', $userId)
            ->count();

        // 🔹 Đếm tổng số bình luận trên các bài viết của user
        $commentsCount = DB::table('post_comments')
            ->join('posts', 'post_comments.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->count();

        // 🔹 Đếm tổng số lượt like trên các bài viết của user
        $likesCount = DB::table('post_likes')
            ->join('posts', 'post_likes.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->count();

        return response()->json([
            'posts' => $postsCount,
            'comments' => $commentsCount,
            'likes' => $likesCount
        ]);
    }
    public function getUserPosts($userId)
    {
        $posts = Post::with('creator')
            ->withCount('likes')       // số lượt thích bài viết
            ->with(['comments.user', 'comments.likes']) // comment + user + like
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }
    public function getUserNotifications($userId)
    {
    $notifications = Notification::with(['fromUser', 'club'])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($notifications);
    }
    public function markAsRead($id)
    {
        $noti = Notification::find($id);

        if (!$noti) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $noti->is_read = 1;
        $noti->save();

        return response()->json(['message' => 'Updated']);
    }
    public function destroyNotification($id)
{
    $noti = Notification::find($id);

    if (!$noti) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $noti->delete();

    return response()->json(['message' => 'Deleted']);
}
public function readCount($userId)
{
    $count = Notification::where('user_id', $userId)
        ->where('is_read', 0)
        ->select('type', 'related_post_id', 'from_user_id')
        ->groupBy('type', 'related_post_id', 'from_user_id')
        ->get()
        ->count();

    return response()->json([
        'read_count' => $count
    ]);
}
public function getJoinedEvents($userId, Request $request)
{
    $clubId = $request->query('club_id'); // Lấy club_id từ query param

    if (!$clubId) {
        return response()->json([
            'error' => 'club_id is required'
        ], 400);
    }

    // Lấy danh sách event mà user tham gia trong câu lạc bộ
    $joinedEvents = EventParticipant::with('event')
        ->where('user_id', $userId)
        ->whereHas('event', function ($query) use ($clubId) {
            $query->where('club_id', $clubId);
        })
        ->get()
        ->map(function ($ep) {
            return $ep->event; // chỉ trả về dữ liệu event
        });

    return response()->json([
        'user_id' => $userId,
        'club_id' => $clubId,
        'events' => $joinedEvents
    ]);

    
}
}