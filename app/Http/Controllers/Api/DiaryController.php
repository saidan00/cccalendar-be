<?php

namespace App\Http\Controllers\Api;

use App\Repositories\DiaryRepository;

class DiaryController extends ApiWithAuthController
{
    public function __construct(DiaryRepository $diaryRepository)
    {
        $this->repository = $diaryRepository;
    }

    protected function getValidationRules()
    {
        return [
            'title' => 'required|max:255'
        ];
    }

    protected function getValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
        ];
    }
}
