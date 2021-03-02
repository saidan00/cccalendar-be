<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\SocialAccount;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleController extends Controller {
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['loginUrl', 'loginCallback']]);
    }

    public function loginUrl() {
        // ở đây chúng ta dùng method stateless() để disable việc sử dụng session để verify state,
        // vì ở route/api.php sẽ không đi qua middleware tạo session nên sẽ không sử dụng được session.
        return Response::json([
            'url' => Socialite::driver('google')
                ->with(['access_type' => 'offline'])->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function loginCallback() {
        // Lấy user từ Google:
        $googleUser = Socialite::driver('google')
            ->with(['access_type' => 'offline'])
            ->stateless()
            ->user();
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
                ]);
                $socialAccount->fill(['user_id' => $user->id])->save();
            }
        });

        // Tạo một jwt token để user có thể đăng nhập
        // return Response::json([
        //     'user' => $user,
        //     'google_user' => $googleUser,
        // ]);
        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user = null) {
        return Response::json([
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function guard() {
        return Auth::Guard('api');
    }
}
