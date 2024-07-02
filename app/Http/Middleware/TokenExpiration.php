<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenExpiration
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
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $tokenCreationTime = $accessToken->created_at;
        $tokenLifetime = 120;
        
        if (now()->diffInMinutes($tokenCreationTime) > $tokenLifetime) {
            $accessToken->delete();
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
