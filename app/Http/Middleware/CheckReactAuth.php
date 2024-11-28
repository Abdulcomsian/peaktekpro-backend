<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;


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
        try {

            $token = $request->query('t');
            // validate token
            $accessToken = PersonalAccessToken::findToken($token);

            if(!$token)
            {
                abort(401, 'Unauthorized user.');
            }
            // if (!$token || !$accessToken) {
            //     abort(401, 'Unauthorized user.');
            // }

            // // Retrieve the user associated with the token
            // $user = $accessToken->tokenable;

            // if (!$user) {
            //     abort(401, 'User not found.');
            // }
            $user = User::with('role')->find(1);

            // Attach user to the request
            $request->replace(['t' => $token, 'user' => $user]);

            return $next($request);

        } catch (\Exception $e) {
            abort(403, 'An error occured on authorization.');
        }

    }
}
