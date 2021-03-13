<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Carbon;

class CalendarServiceHelper
{
    protected $client;
    protected $calendarService;
    protected $timezone = 'Asia/Ho_Chi_Minh';

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        // Set token for the Google API PHP Client
        $google_client_token = [
            'access_token' => $token,
            'expires_in' => 3600
        ];

        $this->client = new Google_Client();
        $this->client->setAccessToken(json_encode($google_client_token));

        $this->calendarService = new Google_Service_Calendar($this->client);
    }

    public function filter(Request $request)
    {
        $events = [];
        $optParams = [];

        if ($request->filled('start')) {
            array_push($optParams, ['timeMin' => $this->convertTime($request->input('start'))]);
        }

        if ($request->filled('end')) {
            array_push($optParams, ['timeMax' => $this->convertTime($request->input('end'))]);
        }

        // lấy danh sách events theo ngày
        $events = $this->calendarService->events->listEvents('primary', $optParams)->getItems();

        return $events;
    }

    private function convertTime($time, $min = 0)
    {
        return Carbon::parse($time, $this->timezone)->addMinutes($min)->toIso8601String();
    }
}
