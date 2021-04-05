<?php

namespace App\Http\Controllers\Api;

use App\Diary;
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
        parent::__construct($diaryRepository);
        $this->tagRepository = $tagRepository;
    }

    public function getResource()
    {
        return DiaryResource::class;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->createOrUpdate($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->createOrUpdate($request, 'update', $id);
    }

    private function createOrUpdate(Request $request, $createOrUpdate = 'create', $id = null)
    {
        // except user_id để tránh việc client gửi request kèm user_id
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            $this->getValidationRules(),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            // lấy user từ middleware VerifyGoogleToken
            $user = $request->get('user');
            $data['user_id'] = $user->id;
            $diary = null;

            // dùng DB::transaction sẽ tự động rollback khi xảy ra lỗi
            DB::transaction(function () use ($data, &$diary, $createOrUpdate, $id) {
                // thêm các tag vào database (nếu trùng thì bỏ qua)
                $this->tagRepository->insertNewTags($data['tags'], $data['user_id']);

                // tạo mới hoặc update diary
                switch ($createOrUpdate) {
                    case 'create':
                        // tạo diary
                        $diary = $this->repository->create($data);
                        break;
                    case 'update':
                        // update diary
                        $diary = $this->repository->update($id, $data, $data['user_id']);
                        // xóa tất cả tag cũ của diary (nếu có)
                        $this->tagRepository->deleteByDiaryId($diary->id);
                        break;
                }

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
            'tags' => 'required|array',
            'tags.*' => 'required'
        ];
    }

    protected function getValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
            'tags.required' => trans('The tags field is required, if there is no tag, please pass an empty array'),
            'tags.array' => trans('The tags must be type of array'),
            'tags.*.required' => trans('The tag name is required'),
        ];
    }
}
