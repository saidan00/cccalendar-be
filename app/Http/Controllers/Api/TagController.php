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
        $diaries = DB::table('diaries')->get();
        $diaryTitles = [];

        foreach ($diaries as $diary) {
            $diaryTitles[] = $diary->title;
        }

        // write to json array of diary title (string)
        Storage::put('event_1.json', json_encode($diaryTitles));

        $commandPath = Storage::path('test.py');
        $command = escapeshellcmd($commandPath);
        $output = shell_exec($command .' 2>&1');

        // get result from "{0" character
        $resultFromOutput = substr($output, strpos($output, '{0'));
        // $diaryClusters = json_decode($resultFromOutput);
        $diaryClusters = json_decode($resultFromOutput);

        foreach($diaryClusters as $key => $value) {
            echo 'Cluster number: ' . $key . '<br/>';
            foreach($value as $diaryIndex) {
                echo $diaries[$diaryIndex]->title . '<br/>';
            }
            echo '<br/>';
        }

        // return $resultFromOutput;
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
