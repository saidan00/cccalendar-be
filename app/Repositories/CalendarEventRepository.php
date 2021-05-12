<?php

namespace App\Repositories;

use App\Components\GoogleServiceCalendarEvent;
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
    protected $userId;

    public function __construct(Request $request)
    {
        $this->token = $request->header('Authorization');

        $this->calendarService = null;
        $this->timezone = config('timezone', 'Asia/Ho_Chi_Minh');
        $this->calendarId = 'primary';
    }

    public function getCalendarService()
    {
        // Set token for the Google API PHP Client
        $google_client_token = [
            'access_token' => $this->token,
            'expires_in' => 3600
        ];

        $this->client = new Google_Client();
        $this->client->setAccessToken(json_encode($google_client_token));

        return new Google_Service_Calendar($this->client);
    }

    public function listColors()
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        $colors = null;

        try {
            $colors = $this->calendarService->colors->get();
        } catch (Exception $e) {
            throw new Exception('No color found');
        }

        return $colors;
    }

    /**
     * Get event by id
     */
    public function getEvent(Request $request, $eventId)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        // if userId is null => get user->id
        $this->userId = $this->userId ?? $request->get('user')->id;

        $event = null;

        try {
            $event = $this->calendarService->events->get($this->calendarId, $eventId);
        } catch (Exception $e) {
            throw new Exception('No event found');
        }

        return $this->mapToGoogleServiceCalendarEvent($event);
    }

    /**
     * List events
     */
    public function listEvents(Request $request)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        // if userId is null => get user->id
        $this->userId = $this->userId ?? $request->get('user')->id;

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

        $events = $this->mapToGoogleServiceCalendarEvents($events);

        // filter by tags
        if ($request->filled('tags')) {
            if ($request->filled('tags')) {
                $tags = $request->input('tags');
                $containAllTag = $request->boolean('containAllTag');
                $tagsCount = count($tags);

                foreach ($events as $key => $event) {
                    $eventTags = [];
                    foreach ($event->tags() as $eventTag) {
                        $eventTags[] = $eventTag->name;
                    }

                    if ($containAllTag === true) {
                        // if eventTags don't contain all tags => unset event
                        if (count(array_intersect($tags, $eventTags)) != $tagsCount) {
                            unset($events[$key]);
                        }
                    } else {
                        // if eventTags contain no tag in tags array => unset event
                        if (count(array_intersect($tags, $eventTags)) == 0) {
                            unset($events[$key]);
                        }
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Insert event - request parameters: title, description, start, end, attendees
     */
    public function insertEvent(Request $request)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        // if userId is null => get user->id
        $this->userId = $this->userId ?? $request->get('user')->id;

        $event = new Google_Service_Calendar_Event([
            'summary' => $request->input('title'),
            'description' => $request->input('description'),
            'start' => [
                'dateTime' => $this->convertTime($request->input('start')),
                'timeZone' => $this->timezone,
            ],
            'end' => [
                'dateTime' => $this->convertTime($request->input('end')),
                'timeZone' => $this->timezone,
            ],
            'attendees' => $this->getAttendees($request->input('attendees')),
            'colorId' => $request->input('colorId'),
        ]);

        // if ($request->filled('recurrence')) {
        //     $event['recurrence'] = [
        //         "RRULE:FREQ=" . strtoupper($request->input('recurrence')),
        //     ];
        // }
        // var_dump($event);

        $event = $this->calendarService->events->insert($this->calendarId, $event);

        return $this->mapToGoogleServiceCalendarEvent($event);
    }

    /**
     * Update event - request parameters: title, description, start, end, attendees
     */
    public function updateEvent(Request $request, string $eventId)
    {
        // if calendarService is null => getCalendarService()
        $this->calendarService = $this->calendarService ?? $this->getCalendarService();

        // if userId is null => get user->id
        $this->userId = $this->userId ?? $request->get('user')->id;

        $event = null;

        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => $request->input('title'),
                'description' => $request->input('description'),
                'start' => [
                    'dateTime' => $this->convertTime($request->input('start')),
                    'timeZone' => $this->timezone,
                ],
                'end' => [
                    'dateTime' => $this->convertTime($request->input('end')),
                    'timeZone' => $this->timezone,
                ],
                'attendees' => $this->getAttendees($request->input('attendees')),
                'colorId' => $request->input('colorId'),
            ]);

            $event = $this->calendarService->events->update($this->calendarId, $eventId, $event);
        } catch (Exception $e) {
            throw new Exception('No event found');
        }

        return $this->mapToGoogleServiceCalendarEvent($event);
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
    private function getAttendees(array $emails = null)
    {
        $attendees = [];

        if ($emails) {
            foreach ($emails as $email) {
                if ($email) {
                    $attendees[] = [
                        'email' => $email
                    ];
                }
            }
        }

        return $attendees;
    }

    /**
     * Map to GoogleServiceCalendarEvent component
     */
    private function mapToGoogleServiceCalendarEvent($event)
    {
        return new GoogleServiceCalendarEvent($event, $this->userId);
    }

    private function mapToGoogleServiceCalendarEvents(array $events)
    {
        $eventsToReturn = [];

        foreach ($events as $event) {
            $eventsToReturn[] = $this->mapToGoogleServiceCalendarEvent($event);
        }

        return $eventsToReturn;
    }

    private function convertTime($time, $min = 0)
    {
        return Carbon::parse($time, $this->timezone)->addMinutes($min)->toIso8601String();
    }
}
