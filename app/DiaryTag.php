<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiaryTag extends Model
{
    protected $table = 'diary_tags';
    protected $id = ['diary_id', 'tag_id'];
}
