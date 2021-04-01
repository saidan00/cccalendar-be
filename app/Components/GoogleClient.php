<?php

namespace App\Components;

use Google_Client as BaseGoogleClient;
use Illuminate\Http\Request;

/**
 * Class GoogleClient
 * @package App\Components
 */
class GoogleClient
{
    /**
     * @var BaseGoogleClient
     */
    protected $client;

    protected $token;

    /**
     * GoogleClient constructor.
     * @param BaseGoogleClient $client
     */
    public function __construct(BaseGoogleClient $client, Request $request)
    {
        $this->client = $client;
        $this->token = $request->header('GoogleAuthorization');
    }

    /**
     * @return BaseGoogleClient
     */
    public function getClient()
    {
        $user = null;

        if (!($user = auth()->user())) {
            return null;
        } else {
            $google_client_token = [
                'access_token' => $this->token,
                'expires_in' => 3600
            ];

            $this->client->setAccessToken(json_encode($google_client_token));

            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            }

            return $this->client;
        }
    }
}
