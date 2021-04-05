<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait AddTagsToModelTrait
{
    public function getTags(array $tagsName, int $user_id)
    {
        $tags = [];

        foreach ($tagsName as $tagName) {
            if ($tagName) {
                $tags[] = $tagName;
                $tags[] = $user_id;
            }
        }

        return $tags;
    }

    public function getTagsQueryBinding(array $tagsName)
    {
        $tagsCount = count($tagsName);
        $queryBinding = '';

        for ($i = 0; $i < $tagsCount; $i++) {
            $queryBinding .= '(?, ?),';
        }

        return substr($queryBinding, 0, -1);
    }

    public function insertNewTags($tagsName, $user_id)
    {
        $tags = $this->getTags($tagsName, $user_id);

        $query = 'INSERT INTO tags (name, user_id) VALUES ' . $this->getTagsQueryBinding($tagsName) . ' ON DUPLICATE KEY UPDATE id=id';

        DB::insert($query, $tags);

        return;
    }
}
