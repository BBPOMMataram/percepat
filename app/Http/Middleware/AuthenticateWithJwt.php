<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('access_token');

        if (!$token) {
            return response()->json(['message' => 'Token not found'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            $externalUserId = $payload['sub'];

            $user = User::firstOrCreate(
                ['external_user_id' => $externalUserId],
                [
                    'name'  => $payload['name'] ?? $externalUserId,
                    'email' => $payload['email'] ?? $externalUserId . '@remove.me',
                    'password' => bcrypt('removeme'),
                ]
            );

            // Set supaya bisa pakai auth()->user(), tapi default guard nya harus set ke 'api' di config/auth.php
            Auth::guard('api')->setUser($user);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token', 'error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
