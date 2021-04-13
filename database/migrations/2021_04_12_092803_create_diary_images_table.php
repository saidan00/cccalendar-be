<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiaryImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diary_images', function (Blueprint $table) {
            $table->foreignId('diary_id');
            $table->foreign('diary_id')->references('id')->on('diaries');

            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('path');
            $table->string('name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diary_images');
    }
}
