<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserToken;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Thiếu token'], 401);
        }

        $token = substr($header, 7);
        $tokenRecord = UserToken::with('user')->where('token', $token)->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Token không hợp lệ'], 401);
        }

        $request->user = $tokenRecord->user;
        return $next($request);
    }
}
