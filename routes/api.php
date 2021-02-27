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

  // Files
  Route::get('files','Api\FileController@get');
  Route::get('files/fetch','Api\FileController@fetch');
  Route::post('file/store','Api\FileController@store');
  Route::delete('file/{file}', 'Api\FileController@destroy');

  // Upload
  Route::post('image/upload','Api\UploadController@image');
  Route::post('file/upload','Api\UploadController@file');
  //Route::get('files/get','Api\UploadController@getFiles');

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

  // Team images
  Route::get('team/image/state/{teamImage}', 'Api\TeamImageController@toggle');
  Route::put('team/image/{teamImage}', 'Api\TeamImageController@coords');
  Route::post('team/image', 'Api\TeamImageController@store');
  Route::post('team/image/order', 'Api\TeamImageController@order');
  Route::delete('team/image/{teamImage}', 'Api\TeamImageController@destroy');

  // Team members
  Route::get('team/members/', 'Api\TeamMemberController@get');
  Route::get('team/member/{teamMember}', 'Api\TeamMemberController@find');
  Route::post('team/member/', 'Api\TeamMemberController@store');
  Route::put('team/member/{teamMember}', 'Api\TeamMemberController@update');
  Route::post('team/member/order', 'Api\TeamMemberController@order');
  Route::get('team/member/state/{teamMember}', 'Api\TeamMemberController@toggle');
  Route::delete('team/member/{teamMember}', 'Api\TeamMemberController@destroy');

  // Team member images
  Route::get('team/member/image/state/{teamMemberImage}', 'Api\TeamMemberImageController@toggle');
  Route::put('team/member/image/{teamMemberImage}', 'Api\TeamMemberImageController@coords');
  Route::post('team/member/image', 'Api\TeamMemberImageController@store');
  Route::post('team/member/image/order', 'Api\TeamMemberImageController@order');
  Route::delete('team/member/image/{teamMemberImage}', 'Api\TeamMemberImageController@destroy');

  // Team
  Route::get('team', 'Api\TeamController@get');
  Route::get('team/{team}', 'Api\TeamController@find');
  Route::post('team', 'Api\TeamController@store');
  Route::put('team/{team}', 'Api\TeamController@update');
  Route::post('team/order', 'Api\TeamController@order');
  Route::get('team/state/{team}', 'Api\TeamController@toggle');
  Route::delete('team/{team}', 'Api\TeamController@destroy');

  // Publication
  Route::get('publications', 'Api\PublicationController@get');
  Route::get('publication/{publication}', 'Api\PublicationController@find');
  Route::post('publication', 'Api\PublicationController@store');
  Route::put('publication/{publication}', 'Api\PublicationController@update');
  Route::post('publication/order', 'Api\PublicationController@order');
  Route::get('publication/state/{publication}', 'Api\PublicationController@toggle');
  Route::delete('publication/{publication}', 'Api\PublicationController@destroy');

  // Contact
  Route::get('contact', 'Api\ContactController@get');
  Route::get('contact/{contact}', 'Api\ContactController@find');
  Route::post('contact', 'Api\ContactController@store');
  Route::put('contact/{contact}', 'Api\ContactController@update');
  Route::post('contact/order', 'Api\ContactController@order');
  Route::get('contact/state/{contact}', 'Api\ContactController@toggle');
  Route::delete('contact/{contact}', 'Api\ContactController@destroy');

  // Contact images
  Route::get('contact/image/state/{contactImage}', 'Api\ContactImageController@toggle');
  Route::put('contact/image/{contactImage}', 'Api\ContactImageController@coords');
  Route::post('contact/image', 'Api\ContactImageController@store');
  Route::post('contact/image/order', 'Api\ContactImageController@order');
  Route::delete('contact/image/{contactImage}', 'Api\ContactImageController@destroy');

  // Assistant
  Route::get('assistants', 'Api\AssistantController@get');
  Route::get('assistant/{assistant}', 'Api\AssistantController@find');
  Route::post('assistant', 'Api\AssistantController@store');
  Route::put('assistant/{assistant}', 'Api\AssistantController@update');
  Route::post('assistant/order', 'Api\AssistantController@order');
  Route::get('assistant/state/{assistant}', 'Api\AssistantController@toggle');
  Route::delete('assistant/{assistant}', 'Api\AssistantController@destroy');

  // Assistant images
  Route::get('assistant/image/state/{assistantImage}', 'Api\AssistantImageController@toggle');
  Route::put('assistant/image/{assistantImage}', 'Api\AssistantImageController@coords');
  Route::post('assistant/image', 'Api\AssistantImageController@store');
  Route::post('assistant/image/order', 'Api\AssistantImageController@order');
  Route::delete('assistant/image/{assistantImage}', 'Api\AssistantImageController@destroy');

});
