<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\DiaryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DiaryController extends Controller
{
    /**
     * @var \App\Repositories\DiaryRepository
     */
    protected $diaryRepository;

    public function __construct(DiaryRepository $diaryRepository)
    {
        $this->diaryRepository = $diaryRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->get('user');

        $diaries = $this->diaryRepository->getAll($user->id);

        return response()->json($diaries);
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
            $this->getStoreDiaryValidationRules(),
            $this->getStoreDiaryValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $request->get('user');
            $data['user_id'] = $user->id;

            $diary = $this->diaryRepository->create($data);

            return response()->json($diary);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = $request->get('user');
        $diary = $this->diaryRepository->find($id, $user->id);

        if (!$diary) {
            return ResponseHelper::response(trans('No diary found'), Response::HTTP_NOT_FOUND);
        } else {
            return response()->json($diary);
        }
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
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            $this->getStoreDiaryValidationRules(),
            $this->getStoreDiaryValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $request->get('user');

            $diary = $this->diaryRepository->update($id, $data, $user->id);

            // if $diary == false
            if (!$diary) {
                return ResponseHelper::response(trans('No diary found'), Response::HTTP_NOT_FOUND);
            }

            return response()->json($diary);
        }
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
        $diary = $this->diaryRepository->delete($id, $user->id);

        if (!$diary) {
            return ResponseHelper::response(trans('No diary found'), Response::HTTP_NOT_FOUND);
        } else {
            return response()->json($diary);
        }
    }

    private function getStoreDiaryValidationRules()
    {
        return [
            'title' => 'required|max:255'
        ];
    }

    private function getStoreDiaryValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
        ];
    }
}
