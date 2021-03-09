<?php

namespace App\Helpers;

use Laravel\Socialite\Facades\Socialite;

class SocialDriver {
    protected $driver;
    protected $provider = 'google';

    public function __construct() {
        $this->driver = Socialite::driver($this->provider)
            ->with(['access_type' => 'offline'])
            ->stateless();
    }

    public function getDriver() {
        return $this->driver;
    }
}
