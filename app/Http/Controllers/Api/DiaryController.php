<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Resources\Diary as DiaryResource;
use App\Repositories\DiaryRepository;
use App\Repositories\TagRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiaryController extends ApiWithAuthController
{
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->get('user');
        $params = $request->only(['title', 'content', 'fromDate', 'toDate', 'tags', 'containAllTag', 'sort', 'itemsPerPage', 'all', 'page']);

        $validator = Validator::make(
            $params,
            $this->getFilterValidationRules()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $entities = $this->repository->filter($params, $user->id);

            return $this->resource::collection($entities);
        }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->get('user');
        $diary = $this->repository->find($id, $user->id);

        if (!$diary) {
            return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
        } else {
            DB::transaction(function () use ($diary, $id, $user) {
                // xoá các diary_tag
                $this->tagRepository->deleteReferenceByDiaryId($diary->id);

                // xoá diary
                $this->repository->delete($id, $user->id);
            });

            return response()->json();
        }
    }

    private function createOrUpdate(Request $request, $createOrUpdate = 'create', $id = null)
    {
        // except user_id để tránh việc client gửi request kèm user_id
        $data = $request->except(['user_id', 'images']);

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

            // nếu request có biến date
            if (isset($data['date'])) {
                $data['created_at'] = $data['date'] . ' ' . Carbon::now(config('timezone', 'Asia/Ho_Chi_Minh'))->format('H:i:s');
            }

            $diary = null;

            // dùng DB::transaction sẽ tự động rollback khi xảy ra lỗi
            DB::transaction(function () use ($data, &$diary, $createOrUpdate, $id) {
                // thêm các tag vào database (nếu trùng thì bỏ qua)
                if (isset($data['tags'])) {
                    $this->tagRepository->insertNewTags($data['tags'], $data['user_id']);
                }

                // tạo mới hoặc update diary
                switch ($createOrUpdate) {
                    case 'create':
                        // tạo diary
                        $diary = $this->repository->create($data);

                        // thêm file
                        if (isset($data['images']) && $diary) {
                            $images = $data['images'];
                            $this->repository->uploadMultipleFiles($images, $data['user_id'], $diary->id);
                        }

                        break;
                    case 'update':
                        // update diary
                        $diary = $this->repository->update($id, $data, $data['user_id']);

                        if ($diary) {
                            // xóa tất cả tag cũ của diary (nếu có)
                            $this->tagRepository->deleteReferenceByDiaryId($diary->id);
                        }

                        break;
                }

                if (isset($data['tags']) && $diary) {
                    // lấy các tag vừa thêm
                    $tags = $this->tagRepository->findByTagsName($data['tags'], $data['user_id']);

                    // thêm các tag mới vào diary
                    $this->repository->addTags($diary->id, $tags, $data['user_id']);
                }
            });

            if ($diary) {
                return new DiaryResource($diary);
            } else {
                return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
            }
        }
    }

    public function addFileToDiary(Request $request, $id)
    {
        // except user_id để tránh việc client gửi request kèm user_id
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            [
                'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            ],
            [
                'image.required' => trans('The image is required'),
                'image.mimes' => trans('The image must be type of jpg, jpeg, png'),
                'image.max' => trans('The size of image must be maximum 2 MB (2048 KB)'),
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            // lấy user từ middleware VerifyGoogleToken
            $user = $request->get('user');
            $data['user_id'] = $user->id;
            $diary = $this->repository->find($id, $data['user_id']);

            if ($diary && isset($data['image'])) {
                DB::transaction(function () use ($data, $id) {
                    $this->repository->uploadSingleFile($data['image'], $data['user_id'], $id);
                });

                return new DiaryResource($diary);
            } else {
                return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
            }
        }
    }

    public function removeFileFromDiary(Request $request, $id)
    {
        // except user_id để tránh việc client gửi request kèm user_id
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            ['file_name' => 'required|string'],
            ['file_name.required' => trans('The file name is required')]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            // lấy user từ middleware VerifyGoogleToken
            $user = $request->get('user');
            $data['user_id'] = $user->id;
            $diary = $this->repository->find($id, $data['user_id']);

            if ($diary) {
                DB::transaction(function () use ($data, $id) {
                    $this->repository->deleteSingleFile($data['file_name'], $data['user_id'], $id);
                });

                return new DiaryResource($diary);
            } else {
                return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
            }
        }
    }

    public function clustering(Request $request)
    {
        $user = $request->get('user');
        $this->repository->kmeansClustering($user->id);
        // $diariesClusters = $this->repository->kmeansClustering(1);
        echo $user->id;
    }

    protected function getValidationRules()
    {
        return [
            'title' => 'required|max:255',
            'tags' => 'array',
            'tags.*' => 'required|string',
            'images' => 'array',
            'images.*' => 'required|mimes:jpg,jpeg,png|max:2048',
            'date' => 'date_format:Y-m-d',
        ];
    }

    protected function getFilterValidationRules()
    {
        return [
            'title' => 'max:100',
            'content' => 'string|max:100',
            'tags' => 'array',
            'tags.*' => 'string|max:100',
            'fromDate' => 'date_format:Y-m-d',
            'toDate' => 'date_format:Y-m-d',
            'itemsPerPage' => 'numeric',
            'sort' => 'in:a-to-z,z-to-a,newest,oldest',
        ];
    }

    protected function getValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
            'tags.array' => trans('The tags must be type of array'),
            'tags.*.required' => trans('The tag name is required'),
            'tags.*.string' => trans('The tag must be type of string'),
            'images.array' => trans('The images must be type of array'),
            'images.*.required' => trans('The image is required'),
            'images.*.mimes' => trans('The image must be type of jpg, jpeg, png'),
            'images.*.max' => trans('The size of image must be maximum 2 MB (2048 KB)'),
            'date.date_format' => trans('The date field must be in format Y-m-d'),
        ];
    }
}
