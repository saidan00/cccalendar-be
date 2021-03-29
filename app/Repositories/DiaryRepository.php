<?php

namespace App\Repositories;

use App\Repositories\EloquentRepository;
use App\Diary;

class DiaryRepository extends EloquentRepository
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
