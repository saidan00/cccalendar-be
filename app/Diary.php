<?php

namespace App;

use App\DiaryTag;
use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $table = "diaries";

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->hasManyThrough(Tag::class, DiaryTag::class, 'diary_id', 'id', 'id', 'tag_id');
    }
}
