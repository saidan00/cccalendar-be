<?php

namespace App\Http\Resources;

use App\Http\Resources\DiaryImage as DiaryImageResource;
use App\Http\Resources\Tag as TagResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Diary extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'date' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'tags' => TagResource::collection($this->tags),
            'images' => DiaryImageResource::collection($this->images),
        ];
    }
}
