<?php

namespace App\Helpers;

use App\SocialAccount;
use Laravel\Socialite\Facades\Socialite;

class SocialDriver
{
    protected $driver;
    protected $provider = 'google';

    public function __construct()
    {
        $this->driver = Socialite::driver($this->provider)
            ->with(['access_type' => 'offline'])
            ->stateless();
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getUser($token)
    {
        $googleUser = $this->driver->userFromToken($token);
        $user = SocialAccount::where("social_id", $googleUser->id)->first()->user;
        return $user;
    }
}
