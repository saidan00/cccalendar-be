<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\SocialAccount;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller {
    public function loginUrl() {
        // ở đây chúng ta dùng method stateless() để disable việc sử dụng session để verify state,
        // vì ở route/api.php sẽ không đi qua middleware tạo session nên sẽ không sử dụng được session.
        return Response::json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function loginCallback() {
        // Lấy user từ Google:
        $googleUser = Socialite::driver('google')->stateless()->user();
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

        // Tạo một jwt token để user có thể đăng nhập, hiện tại response này chỉ để test, chưa trả về jwt
        return Response::json([
            'user' => $user,
            'google_user' => $googleUser,
        ]);
    }
}
