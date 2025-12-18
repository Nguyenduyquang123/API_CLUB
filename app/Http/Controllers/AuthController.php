<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Str;

class AuthController extends Controller
{
   
public function register(Request $request)
{
   
    $this->validate($request, [
        'username' => 'required|string|max:255|unique:users',
        'email' => 'required|email|max:255|unique:users',
        'displayName' => 'nullable|string|max:255', 
        'password' => 'required|string|min:6|confirmed', 
        'password_confirmation' => 'required|string|min:6', 
    ]);


    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'hashedPassword' => \Illuminate\Support\Facades\Hash::make($request->password), 
        'displayName' => $request->displayName ?? $request->username,

    ]);

    return response()->json(['message' => 'Đăng ký thành công', 'user' => $user]);
}

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !password_verify($request->hashedPassword, $user->hashedPassword)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

       UserToken::create(['user_id' => $user->id, 'token' => $token]);

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json(['user' => $request->user]);
    }

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
            $user->api_token = null;
            $user->save();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
