<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class EventRepository
{
    public function addTags($id, $tags)
    {
        $event_tags = $this->getTagsToInsertEventTags($id, $tags);
        DB::table('event_tags')->insert($event_tags);
    }

    /**
     * return mảng tags [['event_id', 'tag_id'], [], ...] để map vào query
     */
    public function getTagsToInsertEventTags($diaryId, $tags)
    {
        $tagsToInsert = [];

        foreach ($tags as $tag) {
            $tagsToInsert[] = [
                'event_id' => $diaryId,
                'tag_id' => $tag->id
            ];
        }

        return $tagsToInsert;
    }
}
