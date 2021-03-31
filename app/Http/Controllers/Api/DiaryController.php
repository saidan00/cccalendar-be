<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SocialDriver;
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

    /**
     * @var \App\Helpers\SocialDriver
     */
    protected $socialDriver;

    public function __construct(DiaryRepository $diaryRepository, SocialDriver $socialDriver)
    {
        $this->diaryRepository = $diaryRepository;
        $this->socialDriver = $socialDriver;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $diaries = $this->diaryRepository->getAll();

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
        $data = $request->all();

        $validator = Validator::make(
            $data,
            $this->getStoreDiaryValidationRules(),
            $this->getStoreDiaryValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $this->socialDriver->getUser();
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
    public function show($id)
    {
        $diary = $this->diaryRepository->find($id);

        return response()->json($diary);
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
            $this->getListDiaryValidationRules(),
            $this->getStoreDiaryValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $diary = $this->diaryRepository->update($id, $data);

            return response()->json($diary);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $isDeleted = $this->diaryRepository->delete($id);

        return response()->json($isDeleted);
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
