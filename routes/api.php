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

  // Home
  Route::get('home', 'Api\HomeController@get');
  Route::get('home/{home}', 'Api\HomeController@find');
  Route::post('home', 'Api\HomeController@store');
  Route::put('home/{home}', 'Api\HomeController@update');
  Route::post('home/order', 'Api\HomeController@order');
  Route::get('home/state/{home}', 'Api\HomeController@toggle');
  Route::delete('home/{home}', 'Api\HomeController@destroy');

  // Home images
  Route::get('home/image/state/{homeImage}', 'Api\HomeImageController@toggle');
  Route::put('home/image/{homeImage}', 'Api\HomeImageController@coords');
  Route::post('home/image', 'Api\HomeImageController@store');
  Route::post('home/image/order', 'Api\HomeImageController@order');
  Route::delete('home/image/{homeImage}', 'Api\HomeImageController@destroy');
 
  // Team categories
  Route::get('team/categories', 'Api\TeamCategoryController@get');
  Route::get('team/category/{teamCategory}', 'Api\TeamCategoryController@find');

  // Team images
  Route::get('team/image/state/{teamImage}', 'Api\TeamImageController@toggle');
  Route::put('team/image/{teamImage}', 'Api\TeamImageController@coords');
  Route::post('team/image', 'Api\TeamImageController@store');
  Route::post('team/image/order', 'Api\TeamImageController@order');
  Route::delete('team/image/{teamImage}', 'Api\TeamImageController@destroy');

  // Team
  Route::get('team', 'Api\TeamController@get');
  Route::get('team/{team}', 'Api\TeamController@find');
  Route::post('team', 'Api\TeamController@store');
  Route::put('team/{team}', 'Api\TeamController@update');
  Route::post('home/order', 'Api\TeamController@order');
  Route::get('team/state/{team}', 'Api\TeamController@toggle');
  Route::delete('team/{team}', 'Api\TeamController@destroy');

});
