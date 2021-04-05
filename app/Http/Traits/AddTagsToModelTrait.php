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

    public function getTagsQueryBinding($tags)
    {
        $tagsCount = count($tags);
        $queryBinding = '';

        for ($i = 0; $i < $tagsCount; $i++) {
            $queryBinding .= '(?, ?),';
        }

        return substr($queryBinding, 0, -1);
    }

    public function insertNewTags($tagsName, $user_id)
    {
        // INSERT INTO cart_items(cart_id, product_id, quantity, created_at, updated_at) VALUES(?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity) + quantity '
        $tags = $this->getTags($tagsName, $user_id);

        $query = 'INSERT INTO tags (name, user_id) VALUES ' . $this->getTagsQueryBinding($tagsName) . ' ON DUPLICATE KEY UPDATE id=id';

        $test = DB::statement($query, $tags);

        return;
    }
}
