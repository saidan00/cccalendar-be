<?php

namespace App\Repositories;

use App\Diary;
use App\Http\Traits\AddTagsToModelTrait;
use App\Repositories\EloquentWithAuthRepository;
use Illuminate\Support\Facades\DB;

class DiaryRepository extends EloquentWithAuthRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Diary::class;
    }

    public function addTags($id, $tags, $user_id)
    {
        $diary = $this->find($id, $user_id);

        if ($diary) {
            $data = $this->getTagsToInsertDiaryTags($id, $tags);
            DB::table('diary_tags')->insert($this->getTagsToInsertDiaryTags($id, $tags));
            return $diary;
        }

        return false;
    }

    public function getTagsToInsertDiaryTags($diaryId, $tags)
    {
        $tagsToInsert = [];

        foreach ($tags as $tag) {
            $tagsToInsert[] = [
                'diary_id' => $diaryId,
                'tag_id' => $tag->id
            ];
        }

        return $tagsToInsert;
    }
}
