<?php

namespace App\Http\Controllers;

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

        // Nếu có gửi mật khẩu cũ → kiểm tra
        if ($request->filled('oldPassword')) {
            if (!Hash::check($request->oldPassword, $user->hashedPassword)) {
                return response()->json(['message' => 'Mật khẩu cũ không đúng'], 400);
            }
        }
        if ($request->filled('password')) {
            $data['hashedPassword'] = Hash::make($request->password);
            DB::table('users')->where('id', $id)->update($data);
            
        }

        // Tạo mảng dữ liệu cần update
        $data = [
            'displayName' => $request->displayName,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'updated_at' => Carbon::now(),
        ];

        // ⚡ Chỉ cập nhật hashedPassword nếu có gửi password mới
      

        DB::table('users')->where('id', $id)->update($data);

        $updatedUser = DB::table('users')->where('id', $id)->first();

        return response()->json([
            'message' => 'User updated successfully',
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

}
