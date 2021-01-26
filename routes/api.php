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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::middleware('auth:sanctum')->group(function() {
  Route::get('user', 'Api\UserController@find');

  // Upload
  Route::post('image/upload','Api\UploadController@image');
  Route::post('file/upload','Api\UploadController@file');
  Route::get('files/get','Api\UploadController@getFiles');

  // News
  Route::get('news', 'Api\NewsController@get');
  Route::get('news/{news}', 'Api\NewsController@find');
  Route::post('news', 'Api\NewsController@store');
  Route::put('news/{news}', 'Api\NewsController@update');
  Route::post('news/order', 'Api\NewsController@order');
  Route::get('news/state/{news}', 'Api\NewsController@toggle');
  Route::delete('news/{news}', 'Api\NewsController@destroy');

  // News images
  Route::get('news/image/state/{newsImage}', 'Api\NewsImageController@toggle');
  Route::put('news/image/{newsImage}', 'Api\NewsImageController@coords');
  Route::post('news/image', 'Api\NewsImageController@store');
  Route::post('news/image/order', 'Api\NewsImageController@order');
  Route::delete('news/image/{newsImage}', 'Api\NewsImageController@destroy');
 
});
