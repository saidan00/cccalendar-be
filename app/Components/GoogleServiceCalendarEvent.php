<?php

namespace App\Components;

use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\DB;

class GoogleServiceCalendarEvent
{
    public $id;
    public $summary;
    public $description;
    public $start;
    public $end;
    public $attendees;

    public function __construct(Google_Service_Calendar_Event $google_Service_Calendar_Event)
    {
        $this->id = $google_Service_Calendar_Event->id;
        $this->summary = $google_Service_Calendar_Event->summary;
        $this->description = $google_Service_Calendar_Event->description;
        $this->start = $google_Service_Calendar_Event->start;
        $this->end = $google_Service_Calendar_Event->end;
        $this->attendees = $google_Service_Calendar_Event->attendees;
    }

    public function tags()
    {
        return DB::select('SELECT tags.* FROM tags JOIN event_tags ON event_tags.tag_id = tags.id WHERE event_id = ?', [$this->id]);
    }
}
