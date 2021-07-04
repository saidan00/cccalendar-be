<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $colors = [
            [
                "colorId" => 1,
                "name" => "Lavender",
                "background" => "#7986cb",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 2,
                "name" => "Sage",
                "background" => "#33b679",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 3,
                "name" => "Grape",
                "background" => "#8e24aa",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 4,
                "name" => "Flamingo",
                "background" => "#e67c73",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 5,
                "name" => "Banana",
                "background" => "#f6c026",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 6,
                "name" => "Tangerine",
                "background" => "#f5511d",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 7,
                "name" => "Peacock",
                "background" => "#039be5",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 8,
                "name" => "Graphite",
                "background" => "#616161",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 9,
                "name" => "Blueberry",
                "background" => "#3f51b5",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 10,
                "name" => "Basil",
                "background" => "#0b8043",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 11,
                "name" => "Tomato",
                "background" => "#d60000",
                "foreground" => "#1d1d1d"
            ]
        ];

        DB::table('colors')->insert($colors);
    }
}
