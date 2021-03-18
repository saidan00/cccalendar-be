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

    public function getEvent($eventId)
    {
        $calendarId = 'primary';
        return $this->calendarService->events->get($calendarId, $eventId);
    }

    public function listEvents(Request $request)
    {
        $events = [];
        $optParams = [];
        $calendarId = 'primary';

        $optParams['singleEvents'] = true;

        // start parameter is not empty
        // must be an RFC3339 timestamp, for example, 2011-06-03T10:00:00-07:00
        if ($request->filled('start')) {
            $optParams['timeMin'] = $this->convertTime($request->input('start'));
        }

        // end parameter is not empty
        // must be an RFC3339 timestamp, for example, 2011-06-03T10:00:00-07:00
        if ($request->filled('end')) {
            $optParams['timeMax'] = $this->convertTime($request->input('end'));
        }

        // searchTerms parameter is not empty
        // find events that match these terms in any field, except for extended properties
        if ($request->filled('searchTerms')) {
            $optParams['q'] = $request->input('searchTerms');
        }

        // orderBy parameter is not empty
        // accepted values: 'startTime', 'updated' (ascending)
        // 'startTime' is only available when parameter singleEvents is True
        $orderByAcceptedValues = ['startTime', 'updated'];
        if ($request->filled('orderBy') && in_array($orderBy = $request->input('orderBy'), $orderByAcceptedValues)) {
            $optParams['orderBy'] = $orderBy;
        }

        // lấy danh sách events theo điều kiện
        $events = $this->calendarService->events->listEvents($calendarId, $optParams)->getItems();

        return $events;
    }

    private function convertTime($time, $min = 0)
    {
        return Carbon::parse($time, $this->timezone)->addMinutes($min)->toIso8601String();
    }
}
