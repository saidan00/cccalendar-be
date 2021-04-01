<?php

namespace App\Helpers;

use App\SocialAccount;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialDriver
{
    protected $driver;
    protected $provider;
    protected $token;

    public function __construct(Request $request)
    {
        $this->provider = 'google';
        $this->token = $request->header('GoogleAuthorization');

        $this->driver = Socialite::driver($this->provider)
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account'])
            ->stateless();
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getUser()
    {
        $googleUser = $this->driver->userFromToken($this->token);
        $user = SocialAccount::where("social_id", $googleUser->id)->first()->user;
        return $user;
    }
}
