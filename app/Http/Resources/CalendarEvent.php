<?php

namespace App\Http\Resources;

use App\Http\Resources\Tag as TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEvent extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'title' => $this->summary,
            'description' => $this->description,
            'start' => $this->start->dateTime ?? $this->start->date,
            'end' => $this->end->dateTime ?? $this->end->date,
            'creator' => $this->creator,
            'attendees' => CalendarEventAttendee::collection($this->attendees),
            'tags' => TagResource::collection($this->tags()),
            'colorId' => $this->colorId,
            'backgroundColor' => $this->backgroundColor,
        ];
    }
}
