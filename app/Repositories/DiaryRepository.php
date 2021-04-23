<?php

namespace App\Repositories;

use App\Diary;
use App\Repositories\EloquentWithAuthRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiaryRepository extends EloquentWithAuthRepository
{
    const FILE_PUBLIC_PATH = 'public/images';
    const FILE_STORAGE_PATH = 'storage/images';

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Diary::class;
    }

    public function filter($params, $user_id = null)
    {
        $diaries = $this->_model->where('user_id', $user_id);
        $itemsPerPage = 10;

        // lọc theo title
        if (isset($params['title'])) {
            $diaries = $diaries->where('title', 'LIKE', '%' . $params['title'] . '%');
        }

        // lọc theo content
        if (isset($params['content'])) {
            $diaries = $diaries->where('content', 'LIKE', '%' . $params['content'] . '%');
        }

        // lọc theo tag
        if (isset($params['tags'])) {
            $diaries = $diaries->whereHas('tags', function ($query) use ($params) {
                return $query->whereIn('name', $params['tags']);
            });
        }

        // lọc theo ngày
        if (isset($params['fromDate'])) {
            $diaries = $diaries->whereDate('created_at', '>=', $params['fromDate']);
        }

        if (isset($params['toDate'])) {
            $diaries = $diaries->whereDate('created_at', '<=', $params['toDate']);
        }

        // sắp xếp
        if (isset($params['sort'])) {
            switch ($params['sort']) {
                case 'a-to-z':
                    $diaries = $diaries->orderBy('title');
                    break;
                case 'z-to-a':
                    $diaries = $diaries->orderBy('title', 'desc');
                    break;
                case 'newest':
                    $diaries = $diaries->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $diaries = $diaries->orderBy('created_at');
                    break;
                default:
                    $diaries = $diaries->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $diaries = $diaries->orderBy('created_at', 'desc');
        }

        // phân trang
        if (isset($params['itemsPerPage'])) {
            $itemsPerPage = $params['itemsPerPage'];
        }
        $diaries = $diaries->paginate($itemsPerPage);

        $diaries->appends($params)->links();

        return $diaries;
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

    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     */
    public function uploadMultipleFiles($files, $userId, $diaryId)
    {
        // if has file(s)
        if (count($files) !== 0) {
            foreach ($files as $file) {
                $this->uploadSingleFile($file, $userId, $diaryId);
            }
        }

        return;
    }

    /**
     * @return array|false
     */
    public function uploadSingleFile(UploadedFile $file, int $userId, int $diaryId)
    {
        $diaryImage = false;

        // tạo chuỗi ngẫu nhiên có độ dài = 6
        $randStr = $this->generateRandomString();

        // tên file = ngày giờ + user id + diary id + chuỗi ngẫu nhiên
        $fileAltText = Carbon::now(config('timezone', 'Asia/Ho_Chi_Minh'))
            ->format('YmdHisu') . '_' . $userId . '_' . $diaryId . '_' . $randStr;

        // file extension
        $extension = $file->extension();

        // file name = alt_text + extension
        $fileName = $fileAltText . '.' . $extension;

        // tạo thư mục lưu trữ images/userId/diaryId/
        $subDirectory = $userId . '/' . $diaryId;
        $directory = $this::FILE_PUBLIC_PATH . '/' . $subDirectory;
        Storage::makeDirectory($directory);

        $uploadSuccess = $file->storeAs($directory, $fileName);

        if ($uploadSuccess) {
            $diaryImage = [
                'diary_id' => $diaryId,
                'user_id' => $userId,
                'path' => $this::FILE_STORAGE_PATH . '/' . $subDirectory . '/' . $fileName,
                'name' => $fileName,
            ];

            $this->storeFileInfo($diaryImage);
        }

        return $diaryImage;
    }

    public function storeFileInfo($diaryImage)
    {
        return DB::table('diary_images')->insert($diaryImage);
    }

    public function deleteSingleFile($fileName, $userId, $id)
    {
        $fileDeleted = Storage::delete($this::FILE_STORAGE_PATH . '/' . $userId . '/' . $fileName);

        if ($fileDeleted) {
            DB::table('diary_images')->where([
                ['diary_id', '=', $id],
                ['user_id', '=', $userId],
                ['name', '=', $fileName],
            ])->delete();
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
