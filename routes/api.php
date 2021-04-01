<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['google.auth']], function () {
    Route::post('logout', 'Api\Auth\GoogleController@logout');
    Route::post('me', 'Api\Auth\GoogleController@me');
});

Route::prefix('calendar')->middleware('auth:api')->group(function () {
    Route::get('/', 'Api\CalendarController@index');
    Route::get('/{id}', 'Api\CalendarController@showEvent');
    Route::post('/', 'Api\CalendarController@createEvent');
    Route::put('/{id}', 'Api\CalendarController@updateEvent');
    Route::delete('/{id}', 'Api\CalendarController@deleteEvent');
});

// ->middleware('google.auth')
Route::prefix('diary')->group(function () {
    Route::get('/', 'Api\DiaryController@index');
    Route::get('/{id}', 'Api\DiaryController@show');
    Route::post('/', 'Api\DiaryController@store');
    Route::put('/{id}', 'Api\DiaryController@update');
    Route::delete('/{id}', 'Api\DiaryController@destroy');
});


Route::get('auth/google/url', 'Api\Auth\GoogleController@loginUrl')->name('login');
Route::get('auth/google/callback', 'Api\Auth\GoogleController@loginCallback');
