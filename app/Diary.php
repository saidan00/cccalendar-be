<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    protected $table = "diaries";

    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
