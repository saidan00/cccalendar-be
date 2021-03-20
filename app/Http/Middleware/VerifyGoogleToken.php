<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Helpers\SocialDriver;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

class VerifyGoogleToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $socialDriver = new SocialDriver();
            $driver = $socialDriver->getDriver();
            $token = $request->header('Authorization');
            $driver->userFromToken($token);
        } catch (Exception $e) {
            $message = trans('Unauthorized');
            return ResponseHelper::response($message, Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
