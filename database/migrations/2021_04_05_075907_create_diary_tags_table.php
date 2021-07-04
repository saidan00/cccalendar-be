<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiaryTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diary_tags', function (Blueprint $table) {
            $table->foreignId('diary_id');
            $table->foreign('diary_id')->references('id')->on('diaries');

            $table->foreignId('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags');

            $table->primary(['diary_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diary_tags');
    }
}
