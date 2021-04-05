<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\AddTagsToModelTrait;
use App\Repositories\DiaryRepository;
use Illuminate\Http\Request;

class DiaryController extends ApiWithAuthController
{
    use AddTagsToModelTrait;

    public function __construct(DiaryRepository $diaryRepository)
    {
        $this->repository = $diaryRepository;
    }

    public function addTags(Request $request)
    {
        $tags = $request->input('tags');
        $this->insertNewTags($tags, 6);
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
