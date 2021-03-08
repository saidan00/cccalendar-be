<?php

use Illuminate\Http\Request;
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

Route::group(['middleware' => 'google.auth'], function ($router) {
    Route::post('logout', 'Api\Auth\GoogleController@logout');
    // Route::post('refresh', 'Api\Auth\GoogleController@refresh');
    Route::post('me', 'Api\Auth\GoogleController@me');
});


Route::get('auth/google/url', 'Api\Auth\GoogleController@loginUrl')->name('login');
Route::get('auth/google/callback', 'Api\Auth\GoogleController@loginCallback');
