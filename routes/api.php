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

Route::group(['middleware' => ['google.auth'], 'prefix' => 'calendar'], function () {
    Route::get('/', 'Api\CalendarController@index');
    // Route::post('me', 'Api\CalendarController@me');
});


Route::get('auth/google/url', 'Api\Auth\GoogleController@loginUrl')->name('login');
Route::get('auth/google/callback', 'Api\Auth\GoogleController@loginCallback');
