<?php

namespace App\Repositories;

use App\Diary;
use App\Repositories\EloquentWithAuthRepository;

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
}
