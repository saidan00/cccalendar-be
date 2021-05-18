<?php

namespace App\Repositories;

use App\Diary;
use App\Repositories\EloquentWithAuthRepository;
use App\Tag;
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

        $issetContainAllTag = isset($params['containAllTag']);
        $issetAll = isset($params['all']);

        if ($issetContainAllTag) {
            $params['containAllTag'] = filter_var($params['containAllTag'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($issetAll) {
            $params['all'] = filter_var($params['all'], FILTER_VALIDATE_BOOLEAN);
        }

        // lọc theo tag
        $tagCount = count($params['tags']);
        if (isset($params['tags']) && $tagCount > 0) {
            if ($issetContainAllTag && $params['containAllTag'] === true) {
                // chọn các diaries.id có chứa tất cả tags cần tìm
                $diaryIdsContainAllTags = DB::table('tags')
                    ->select('diary_tags.diary_id')
                    ->join('diary_tags', 'tags.id', '=', 'diary_tags.tag_id')
                    ->where('tags.user_id', '=', $user_id)
                    ->whereIn('tags.name', $params['tags'])
                    ->groupBy('diary_tags.diary_id')
                    ->havingRaw('COUNT(tags.name) = ?', [$tagCount])
                    ->pluck('diary_tags.diary_id')->toArray();

                $diaries = $diaries->whereIn('id', $diaryIdsContainAllTags);
            } else {
                // chọn các diaries có chứa 1 trong các tags cần tìm
                $diaries = $diaries->whereHas('tags', function ($query) use ($params) {
                    return $query->whereIn('name', $params['tags']);
                });
            }
        }

        // lọc theo ngày
        if (isset($params['fromDate'])) {
            $diaries = $diaries->whereDate('created_at', '>=', $params['fromDate']);
        }

        if (isset($params['toDate'])) {
            $diaries = $diaries->whereDate('created_at', '<=', $params['toDate']);
        }

        // lọc theo title
        if (isset($params['title'])) {
            $diaries = $diaries->where('title', 'LIKE', '%' . $params['title'] . '%');
        }

        // lọc theo content
        if (isset($params['content'])) {
            $diaries = $diaries->where('content', 'LIKE', '%' . $params['content'] . '%');
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

        // nếu không fetch all
        if ($issetAll && $params['all'] === true) {
            $diaries = $diaries->get();
        } else {
            // phân trang
            if (isset($params['itemsPerPage'])) {
                $itemsPerPage = $params['itemsPerPage'];
            }
            $diaries = $diaries->paginate($itemsPerPage);

            $diaries->appends($params)->links();
        }

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

    public function kmeansClustering($user_id = null)
    {
        if ($user_id) {
            $diaries = DB::table('diaries')->where('user_id', '=', $user_id)->get();

            if (count($diaries) > 0) {
                $diaryTitles = [];

                foreach ($diaries as $diary) {
                    $diaryTitles[] = $diary->title;
                }

                $fileName = "tmp_diary_$user_id.json";

                // delete file if exists
                Storage::delete($fileName);

                // write new file
                Storage::put($fileName, json_encode($diaryTitles));

                $commandPath = Storage::path('kmeans.py');
                $command = escapeshellcmd($commandPath);
                $output = shell_exec($command . " diary $user_id 2>&1");

                // delete file if exists
                Storage::delete($fileName);

                if ($output) {
                    $diaryClusters = json_decode($output);
                    $randomString = $this->generateRandomString();
                    foreach ($diaryClusters as $key => $diaryIndexs) {
                        $tagName = 'diary_' . strtolower($randomString) . '_' . ($key + 1);
                        $tag = [$tagName, $user_id];

                        // tránh auto increment id khi insert on duplicate
                        // set auto_increment = max(id) + 1
                        DB::statement("SET @NEW_AI = IFNULL((SELECT MAX(`id`) + 1 FROM `tags`),1);");
                        DB::statement("SET @ALTER_SQL = CONCAT('ALTER TABLE `tags` AUTO_INCREMENT =', @NEW_AI);");
                        DB::statement("PREPARE NEWSQL FROM @ALTER_SQL;");
                        DB::statement("EXECUTE NEWSQL;");

                        $query = 'INSERT INTO tags (name, user_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=id';

                        DB::insert($query, $tag);

                        $tag = Tag::where([
                            ['user_id', '=', $user_id],
                            ['name', 'LIKE', $tagName],
                        ])->first();

                        foreach ($diaryIndexs as $diaryIndex) {

                            $tagToInsert = [
                                'diary_id' => $diaries[$diaryIndex]->id,
                                'tag_id' => $tag->id
                            ];

                            DB::table('diary_tags')->insert($tagToInsert);
                        }
                    }
                }
            }
            return;
        } else {
            return null;
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
