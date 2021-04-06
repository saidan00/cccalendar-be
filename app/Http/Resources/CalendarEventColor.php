<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventColor extends JsonResource
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
        $colors = [];

        foreach ($this->resource as $key => $color) {
            $background = $color->getBackground();
            $foreground = $color->getForeground();

            $colors[] = [
                'colordId' => $key,
                'background' => $background,
                'foreground' => $foreground,
            ];
        }

        return $colors;
    }
}
