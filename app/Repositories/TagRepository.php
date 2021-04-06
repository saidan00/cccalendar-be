<?php

namespace App\Repositories;

use App\Repositories\EloquentWithAuthRepository;
use App\Tag;
use Illuminate\Support\Facades\DB;

class TagRepository extends EloquentWithAuthRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Tag::class;
    }

    public function findByTagsName(array $tagsName, $user_id = null)
    {
        $result = $this->_model->where('user_id', $user_id)
            ->whereIn('name', $tagsName)
            ->get();

        return $result;
    }

    public function deleteReferenceByDiaryId($diaryId)
    {
        DB::table('diary_tags')->where('diary_id', '=', $diaryId)->delete();
    }

    public function deleteReferenceByTagId($tagId)
    {
        DB::table('diary_tags')->where('tag_id', '=', $tagId)->delete();
    }

    // trả về mảng [$tagName, $userId, $tagName, $userId, ...]
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

    // trả về chuỗi để bind query (?, ?), (?, ?), ...
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

        // tránh auto increment id khi insert on duplicate
        // set auto_increment = max(id) + 1
        DB::statement("SET @NEW_AI = IFNULL((SELECT MAX(`id`) + 1 FROM `tags`),1);");
        DB::statement("SET @ALTER_SQL = CONCAT('ALTER TABLE `tags` AUTO_INCREMENT =', @NEW_AI);");
        DB::statement("PREPARE NEWSQL FROM @ALTER_SQL;");
        DB::statement("EXECUTE NEWSQL;");

        $query = 'INSERT INTO tags (name, user_id) VALUES ' . $this->getTagsQueryBinding($tagsName) . ' ON DUPLICATE KEY UPDATE id=id';

        DB::insert($query, $tags);

        return;
    }
}
