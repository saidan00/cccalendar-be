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
                "background" => "#a4bdfc",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 2,
                "name" => "Sage",
                "background" => "#7ae7bf",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 3,
                "name" => "Grape",
                "background" => "#dbadff",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 4,
                "name" => "Flamingo",
                "background" => "#ff887c",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 5,
                "name" => "Banana",
                "background" => "#fbd75b",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 6,
                "name" => "Tangerine",
                "background" => "#ffb878",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 7,
                "name" => "Peacock",
                "background" => "#46d6db",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 8,
                "name" => "Graphite",
                "background" => "#e1e1e1",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 9,
                "name" => "Blueberry",
                "background" => "#5484ed",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 10,
                "name" => "Basil",
                "background" => "#51b749",
                "foreground" => "#1d1d1d"
            ],
            [
                "colorId" => 11,
                "name" => "Tomato",
                "background" => "#dc2127",
                "foreground" => "#1d1d1d"
            ]
        ];

        DB::table('colors')->insert($colors);
    }
}
