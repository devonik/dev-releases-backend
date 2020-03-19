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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('tech')->name('tech/')->group(static function() {
    Route::get('/getAll', 'Api\TechController@getAll');
    Route::post('/add', 'Api\TechController@add');
    Route::post('/addImageToTech', 'Api\TechController@addImageToTech');
});

Route::prefix('test')->name('test/')->group(static function() {
    Route::get('/testFirebaseMessage', 'Api\TestController@testFirebaseMessage');
    Route::get('/getServerTimestamp', 'Api\TestController@getServerTimestamp');
});

