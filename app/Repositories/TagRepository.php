<?php

namespace App\Repositories;

use App\Http\Traits\AddTagsToModelTrait;
use App\Repositories\EloquentWithAuthRepository;
use App\Tag;
use Illuminate\Support\Facades\DB;

class TagRepository extends EloquentWithAuthRepository
{
    use AddTagsToModelTrait;

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
}
