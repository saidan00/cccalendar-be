<?php

namespace App;

use App\Tag;
use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    protected $table = 'user_tags';
    protected $primaryKey = ['user_id', 'tag_id'];
}
