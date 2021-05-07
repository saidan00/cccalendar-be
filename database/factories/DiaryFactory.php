<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Diary;
use Faker\Generator as Faker;

$factory->define(Diary::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(50),
        'content' => $faker->realText,
        'user_id' => 1
    ];
});
