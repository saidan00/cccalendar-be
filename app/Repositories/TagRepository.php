<?php

namespace App\Repositories;

use App\Repositories\EloquentWithAuthRepository;
use App\Tag;

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
}
