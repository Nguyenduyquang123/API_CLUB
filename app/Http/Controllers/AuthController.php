<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Đăng ký
public function register(Request $request)
{
    // 1. Cập nhật Quy tắc Xác thực (Validation Rules)
    $this->validate($request, [
        'username' => 'required|string|max:255|unique:users',
        'email' => 'required|email|max:255|unique:users',
        // Thêm trường nickname, ví dụ: không bắt buộc, tối đa 255 ký tự
        'displayName' => 'nullable|string|max:255', 
        
        // Mật khẩu phải khớp với trường 'password_confirmation'
        // Tôi đã đổi tên 'hashedPassword' thành 'password' cho chuẩn Laravel
        'password' => 'required|string|min:6|confirmed', 
        'password_confirmation' => 'required|string|min:6', // Trường nhập lại mật khẩu
    ]);

    // 2. Tạo Người dùng (User Creation)
    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        
        // Sử dụng $request->password vì trường gửi lên giờ tên là 'password'
        // Laravel khuyến nghị dùng Hash::make() thay vì password_hash()
        'hashedPassword' => \Illuminate\Support\Facades\Hash::make($request->password), 
        
        // Thêm trường nickname (lưu ý: đảm bảo cột này có trong database)
        'displayName' => $request->displayName ?? $request->username,

    ]);

    // Trả về response JSON
    return response()->json(['message' => 'Đăng ký thành công', 'user' => $user]);
}

    // Đăng nhập
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !password_verify($request->hashedPassword, $user->hashedPassword)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        $token = Str::random(60);
        // Lưu token vào bảng users
        $user->api_token = $token;
        $user->save();

       UserToken::create(['user_id' => $user->id, 'token' => $token]);

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // Lấy thông tin người dùng
    public function profile(Request $request)
    {
        return response()->json(['user' => $request->user]);
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        $user = $request->user;
        $header = $request->header('Authorization');
        $token = substr($header, 7);

        UserToken::where('user_id', $user->id)->where('token', $token)->delete();

          $user = User::where('api_token', $token)->first();

            if (!$user) {
                return response()->json(['message' => 'Token không hợp lệ'], 401);
            }

            // Xóa token khỏi bảng users
            $user->api_token = null;
            $user->save();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
