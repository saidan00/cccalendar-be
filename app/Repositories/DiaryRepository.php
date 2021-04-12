<?php

namespace App\Repositories;

use App\Diary;
use App\Repositories\EloquentWithAuthRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiaryRepository extends EloquentWithAuthRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Diary::class;
    }

    public function addTags($id, $tags, $user_id)
    {
        $diary = $this->find($id, $user_id);

        if ($diary) {
            $data = $this->getTagsToInsertDiaryTags($id, $tags);
            DB::table('diary_tags')->insert($data);
            return $diary;
        }

        return false;
    }

    /**
     * return mảng tags [['diary_id', 'tag_id'], [], ...] để map vào query
     */
    public function getTagsToInsertDiaryTags($diaryId, $tags)
    {
        $tagsToInsert = [];

        foreach ($tags as $tag) {
            $tagsToInsert[] = [
                'diary_id' => $diaryId,
                'tag_id' => $tag->id
            ];
        }

        return $tagsToInsert;
    }

    public function uploadMultiFiles(array $files = [], $userId, $diaryId)
    {
        // if has file(s)
        if (count($files) !== 0) {
            foreach ($files as $file) {
                // tạo chuỗi ngẫu nhiên có độ dài = 6
                $randStr = $this->generateRandomString();
                // tên file = ngày giờ + user id + diary id + chuỗi ngẫu nhiên
                $file_alt_text = Carbon::now(config('timezone', 'Asia/Ho_Chi_Minh'))
                    ->format('YmdHis') . '_' . $userId . '_' . $diaryId . '_' . $randStr;
                return;
            }
        }
    }

    private function generateRandomString()
    {
        // Output: 54ESMD
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';


        $randStr = substr(str_shuffle($permitted_chars), 0, 6);
        return $randStr;
    }
}
