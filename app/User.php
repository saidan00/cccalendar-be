<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function socialAccount()
    {
        return $this->hasOne('App\SocialAccount', 'user_id', 'id');
    }

    public function tags()
    {
        // biến thứ 3 là foreign key của model trung gian (model UserTag)
        // biến thứ 4 là foreign key của model cuối cùng (model Tag)
        // biến thứ 5 là local key (primary key của user)
        // biến thứ 6 là local key (primary key của tag)
        // return $this->hasManyThrough(Tag::class, UserTag::class, 'user_id', 'tag_id', 'id', 'id');

        return $this->hasMany(Tag::class, '$user_id', '$id');
    }
}
