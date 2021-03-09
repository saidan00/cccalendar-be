<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Google_Service_Calendar;
use Illuminate\Support\Carbon;

class CalendarServiceHelper {
    protected $calendarService;
    protected $timezone = 'Asia/Ho_Chi_Minh';

    public function __construct(Google_Service_Calendar $google_Service_Calendar) {
        $this->calendarService = $google_Service_Calendar;
    }

    public function filter(Request $request) {
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

    private function convertTime($time, $min = 0) {
        return Carbon::parse($time, $this->timezone)->addMinutes($min)->toIso8601String();
    }
}
