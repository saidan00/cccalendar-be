<?php

namespace App\Http\Controllers\Api\Auth;

use App\SocialAccount;
use App\User;
use App\Http\Resources\User as UserResource;
use App\Http\Controllers\Controller;
use App\Helpers\SocialDriver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Google_Service_Calendar;

class GoogleController extends Controller
{
    protected $driver;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $socialDriver = new SocialDriver();
        $this->driver = $socialDriver->getDriver();
    }

    public function loginUrl()
    {
        // ở đây chúng ta dùng method stateless() để disable việc sử dụng session để verify state,
        // vì ở route/api.php sẽ không đi qua middleware tạo session nên sẽ không sử dụng được session.
        return Response::json([
            'url' => $this->driver
                ->scopes([Google_Service_Calendar::CALENDAR])
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    public function loginCallback()
    {
        // Lấy user từ Google:
        $googleUser = $this->driver->user();
        $user = null;

        DB::transaction(function () use ($googleUser, &$user) {
            // Tạo đối tượng model SocialAccount
            $socialAccount = SocialAccount::firstOrNew(
                ['social_id' => $googleUser->getId(), 'social_provider' => 'google'],
                ['social_name' => $googleUser->getName()]
            );

            // nếu tài khoản này chưa liên kết với user nào thì sẽ tạo một tài khoản user mới
            if (!($user = $socialAccount->user)) {
                $user = User::create([
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
                $socialAccount->fill(['user_id' => $user->id])->save();
            } else {
                $user->email = $googleUser->getEmail();
                $user->name = $googleUser->getName();
                $user->avatar = $googleUser->getAvatar();
            }
            $user->remember_token = $googleUser->refreshToken;
            $user->save();
        });

        // Tạo một jwt token để user có thể đăng nhập
        $token = $googleUser->token;
        // $test = Socialite::driver('google')->userFromToken($googleUser->token);
        return $this->respondWithToken($token, $user, $googleUser->expiresIn);
        // return Response::json([
        //     // 'user' => $test,
        //     'google_user' => $googleUser,
        // ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $googleUser = $this->driver->userFromToken($request->header('Authorization'));
        $user = User::where('email', $googleUser->getEmail())->first();
        return new UserResource($user);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user, $expiresIn)
    {
        $userObject = isset($user) ? new UserResource($user) : null;

        return Response::json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $userObject,
            'expires_in' => $expiresIn
        ]);
    }

    public function guard()
    {
        return Auth::Guard('api');
    }
}
