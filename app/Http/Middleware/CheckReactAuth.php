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
            $token = $request->query('t') ?? '';
            $jobId = $request->query('j') ?? '';

            if (Auth::check()) {
            return $next($request);
            } else {

                // If the token is missing, abort with an error
                if (!$token) {
                    abort(401, 'Token is required.');
                }

                // Find the token in the database (Personal Access Token or similar)
                $accessToken = PersonalAccessToken::findToken($token);

                // If the token is invalid or not found, abort with Unauthorized
                if (!$accessToken) {
                    abort(401, 'Unauthorized user.');
                }

                // Retrieve the user associated with the token
                $user = $accessToken->tokenable;  // Assuming tokenable is the user model

                // If no user found, abort with Unauthorized
                if (!$user) {
                    abort(401, 'User not found.');
                }

                // Attach the user to the request
                $request->attributes->add(['user' => $user]);

                // Optionally: log the user in (optional for custom login process)
                Auth::login($user); // Logs in the user, so they are authenticated for the session

                if ($jobId) {
                    session(['job_id' => $jobId]);
                }

                return $next($request);
            }
            // Get the token from the request header or query
        } catch (\Exception $e) {
            // Catch any error and abort with a 403 Forbidden
            abort(403, 'An error occurred during authorization.');
        }
    }
}
