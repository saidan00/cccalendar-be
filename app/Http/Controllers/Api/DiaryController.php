<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SocialDriver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DiaryRepository;

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
        $token = $request->header('Authorization');

        //... Validation here

        $user = $this->socialDriver->getUser($token);
        $diary = $this->diaryRepository->create($data);

        return response()->json($diary);
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
        $data = $request->all();

        //... Validation here

        $diary = $this->diaryRepository->update($id, $data);

        return response()->json($diary);
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
}