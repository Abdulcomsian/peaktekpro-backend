<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckReactAuth
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
        $token = $request->query('t');
        $accessToken = PersonalAccessToken::findToken($token);

        dd($accessToken);

        if (!$token) {
            return redirect('/login')->with('error', 'Unauthorized');
        }

        try {

            if (str_starts_with($token, 'Bearer ')) {
                $token = str_replace('Bearer ', '', $token);
            }

            // $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256')); // Validate the token
            // $request->user = $decoded; // Attach the user
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Invalid token');
        }

        return $next($request);

    }
}
