<?php

namespace App\Http\Resources;

use App\Http\Resources\Tag as TagResource;
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
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
