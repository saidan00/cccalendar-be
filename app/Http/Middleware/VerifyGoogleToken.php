<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class VerifyGoogleToken {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        try {
            Socialite::driver('google')->userFromToken($request->header('Authorization'));
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthorized.'], 401);
            // return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
        return $next($request);
    }
}
