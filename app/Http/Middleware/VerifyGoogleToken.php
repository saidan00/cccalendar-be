<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use App\Helpers\SocialDriver;
use Closure;
use Exception;
use Illuminate\Http\Request;
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
    public function handle(Request $request, Closure $next)
    {
        try {
            $socialDriver = new SocialDriver($request);

            $user = $socialDriver->getUser();

            // paste to controller
            $request->attributes->add(['user' => $user]);
        } catch (Exception $e) {
            $message = trans('Unauthorized');
            return ResponseHelper::response($message, Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
