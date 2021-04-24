<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ApiWithAuthController;
use App\Http\Resources\TagFull as TagResource;
use App\Repositories\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TagController extends ApiWithAuthController
{
    public function __construct(TagRepository $tagRepository)
    {
        parent::__construct($tagRepository);
    }

    public function getResource()
    {
        return TagResource::class;
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
        $tag = $this->repository->find($id, $user->id);

        if (!$tag) {
            return ResponseHelper::response(trans('Not found'), Response::HTTP_NOT_FOUND);
        } else {
            DB::transaction(function () use ($tag, $id, $user) {
                // xoá các diary_tag và event_tag
                $this->repository->deleteReferenceByTagId($tag->id);

                // xoá tag
                $this->repository->delete($id, $user->id);
            });

            return response()->json();
        }
    }

    protected function getValidationRules()
    {
        return [];
    }

    protected function getValidationMessages()
    {
        return [];
    }
}
