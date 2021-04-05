<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\EloquentWithAuthRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

abstract class ApiWithAuthController extends Controller
{
    /**
     * @var \App\Repositories\EloquentWithAuthRepository
     */
    protected $repository;

    public function __construct(EloquentWithAuthRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->get('user');

        $entities = $this->repository->getAll($user->id);

        return response()->json($entities);
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

            $entity = $this->repository->create($data);

            return response()->json($entity);
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
        $entity = $this->repository->find($id, $user->id);

        if (!$entity) {
            return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
        } else {
            return response()->json($entity);
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
            $this->getValidationRules(),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $user = $request->get('user');

            $entity = $this->repository->update($id, $data, $user->id);

            // if $entity == false
            if (!$entity) {
                return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
            }

            return response()->json($entity);
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
        $entity = $this->repository->delete($id, $user->id);

        if (!$entity) {
            return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
        } else {
            return response()->json($entity);
        }
    }

    abstract protected function getValidationRules();
    abstract protected function getValidationMessages();
}
