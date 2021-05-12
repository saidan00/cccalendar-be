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
    public $creator;
    public $attendees;
    public $colorId;
    public $backgroundColor;
    public $userId;

    public function __construct(Google_Service_Calendar_Event $google_Service_Calendar_Event, $user_id)
    {
        $this->id = $google_Service_Calendar_Event->getId();
        $this->summary = $google_Service_Calendar_Event->getSummary();
        $this->description = $google_Service_Calendar_Event->getDescription();
        $this->start = $google_Service_Calendar_Event->getStart();
        $this->end = $google_Service_Calendar_Event->getEnd();
        $this->creator = $google_Service_Calendar_Event->getCreator()->getEmail();
        $this->attendees = $google_Service_Calendar_Event->getAttendees();
        $this->colorId = $google_Service_Calendar_Event->getColorId() ?? 7;
        $this->backgroundColor = $this->color($google_Service_Calendar_Event->getColorId());

        $this->userId = $user_id;
    }

    public function tags()
    {
        return DB::select('SELECT tags.* FROM tags JOIN event_tags ON event_tags.tag_id = tags.id WHERE event_id = ? AND tags.user_id = ?', [$this->id, $this->userId]);
    }

    public function color($colorId)
    {
        $color = DB::table('colors')->where('colorId', '=', $colorId)->first();

        if (!$color) {
            $color = DB::table('colors')->where('name', '=', 'Peacock')->first();
        }

        return $color->background;
    }
}
