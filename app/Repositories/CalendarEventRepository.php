<?php

namespace App\Repositories;

use App\Components\GoogleClient;
use Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarEventRepository
{
    protected $client;
    protected $calendarService;
    protected $timezone;
    protected $calendarId;
    protected $token;
    protected $googleClient;

    public function __construct(GoogleClient $googleClient)
    {
        $this->calendarService = null;
        $this->timezone = 'Asia/Ho_Chi_Minh';
        $this->calendarId = 'primary';

        $this->googleClient = $googleClient;
    }

    public function getCalendarService()
    {
        $this->client = $this->googleClient->getClient();
        return new Google_Service_Calendar($this->client);
    }

    /**
     * Get event by id
     */
    public function getEvent($eventId)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $event = null;

        try {
            $event = $this->calendarService->events->get($this->calendarId, $eventId);
        } catch (Exception $e) {
            throw new Exception('No event found');
        }

        return $event;
    }

    /**
     * List events
     */
    public function listEvents(Request $request)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $events = [];
        $optParams = [];

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
        $events = $this->calendarService->events->listEvents($this->calendarId, $optParams)->getItems();

        return $events;
    }

    /**
     * Insert event - request parameters: title, description, start, end, attendees
     */
    public function insertEvent(Request $request)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $event = new Google_Service_Calendar_Event([
            'summary' => $request->input('title'),
            'description' => $request->input('description'),
            'start' => [
                'dateTime' => $this->convertTime($request->input('start'))
            ],
            'end' => [
                'dateTime' => $this->convertTime($request->input('end'))
            ],
            'attendees' => $this->getAttendees($request->input('attendees')),
        ]);

        $event = $this->calendarService->events->insert($this->calendarId, $event);

        return $event;
    }

    /**
     * Update event - request parameters: title, description, start, end, attendees
     */
    public function updateEvent(Request $request, string $eventId)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $event = null;

        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => $request->input('title'),
                'description' => $request->input('description'),
                'start' => [
                    'dateTime' => $this->convertTime($request->input('start'))
                ],
                'end' => [
                    'dateTime' => $this->convertTime($request->input('end'))
                ],
                'attendees' => $this->getAttendees($request->input('attendees')),
            ]);

            $event = $this->calendarService->events->update($this->calendarId, $eventId, $event);
        } catch (Exception $e) {
            throw new Exception('No event found');
        }

        return $event;
    }

    public function deleteEvent($eventId)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $event = null;

        try {
            $event = $this->calendarService->events->delete($this->calendarId, $eventId);
        } catch (Exception $e) {
            throw new Exception('No event found');
        }

        return $event;
    }


    /**
     * Create attendees array from emails array
     */
    private function getAttendees(array $emails)
    {
        $attendees = [];

        foreach ($emails as $email) {
            if ($email) {
                $attendees[] = [
                    'email' => $email
                ];
            }
        }

        return $attendees;
    }

    private function convertTime($time, $min = 0)
    {
        return Carbon::parse($time, $this->timezone)->addMinutes($min)->toIso8601String();
    }
}
