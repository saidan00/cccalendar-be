<?php

use Illuminate\Database\Seeder;
use App\Diary;

class DiarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Diary::class, 100)->create();
    }
}
