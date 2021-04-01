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
    protected $user;

    public function __construct(Request $request)
    {
        $this->user = null;

        $this->provider = 'google';
        $this->token = $request->header('Authorization');

        $this->driver = Socialite::driver($this->provider)
            ->with(['access_type' => 'offline'])
            ->stateless();
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getUser()
    {
        // if user == null
        if (!$this->user) {
            $googleUser = $this->driver->userFromToken($this->token);
            $this->user = SocialAccount::where("social_id", $googleUser->id)->first()->user;
        }

        return $this->user;
    }
}
