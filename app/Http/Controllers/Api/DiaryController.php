<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Diary as DiaryResource;
use App\Http\Traits\AddTagsToModelTrait;
use App\Repositories\DiaryRepository;
use App\Repositories\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiaryController extends ApiWithAuthController
{
    use AddTagsToModelTrait;

    private TagRepository $tagRepository;

    public function __construct(DiaryRepository $diaryRepository, TagRepository $tagRepository)
    {
        $this->repository = $diaryRepository;
        $this->tagRepository = $tagRepository;
    }

    public function addTags(Request $request)
    {
        $tags = $request->input('tags');
        $this->insertNewTags($tags, 6);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            $this->getValidationRules(),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $request->get('user');
            $data['user_id'] = $user->id;
            $diary = null;

            DB::transaction(function () use ($data, &$diary) {
                // thêm các tag vào database (nếu trùng thì bỏ qua)
                $this->tagRepository->insertNewTags($data['tags'], $data['user_id']);

                // tạo diary
                $diary = $this->repository->create($data);

                // xóa tất cả tag cũ của diary (nếu có)

                // lấy các tag vừa thêm
                $tags = $this->tagRepository->findByTagsName($data['tags'], $data['user_id']);

                // thêm các tag mới vào diary
                $this->repository->addTags($diary->id, $tags, $data['user_id']);
            });

            return new DiaryResource($diary);
        }
    }

    protected function getValidationRules()
    {
        return [
            'title' => 'required|max:255',
            'tags' => 'array',
            'tags.*' => 'required'
        ];
    }

    protected function getValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
            'tags.array' => trans('The tags must be type of array'),
            'tags.*.required' => trans('The tag name is required'),
        ];
    }
}
