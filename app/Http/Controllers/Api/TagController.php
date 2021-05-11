<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\ApiWithAuthController;
use App\Http\Resources\TagFull as TagResource;
use App\Repositories\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function update(Request $request, $id)
    {
        $data = $request->only(['name', 'id']);
        $user = $request->get('user');
        $tag = $this->repository->findByTagName($data['name'], $data['id'], $user->id);
        if (!$tag) {
            return parent::update($request, $id);
        } else {
            return ResponseHelper::response(trans('Tag name already exist'), Response::HTTP_BAD_REQUEST);
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

    public function testPy()
    {
        $diaries = DB::table('diaries')->get()->toJson();

        $commandPath = Storage::path('test.py');
        $command = escapeshellcmd($commandPath);
        // shell_exec('chmod 666 ' . $commandPath);
        $output = shell_exec($command . ' ' . "'$diaries' 2>&1");
        // $output = shell_exec($command .' 2>&1');
        // $output = 123;
        return $output;
        // return $diaries;
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
