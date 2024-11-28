<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRoleMiddleware
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
            $user = $request->user ?? '';
            if(!$user || !$user->role || $user->role->id !== 7)
            {

                if($request->expectsJson()){
                    return response()->json(['message' => 'You are not authorized to access this resource'],403);
                }
                else{
                    abort(403,'You are not authorized to access this resource.');
                }
            }

            return $next($request);

        } catch (\Throwable $th) {

            if($request->expectsJson()){
                return response()->json(['message' => 'An error occured while checking resource'],403);
            }
            else{
                abort(403,'An error occured while checking resource.');
            }

        }

    }
}
