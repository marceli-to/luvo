<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\HomeImageController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TeamImageController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TeamMemberImageController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactImageController;
use App\Http\Controllers\Api\AssistantController;
use App\Http\Controllers\Api\AssistantImageController;

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

Route::middleware('auth:sanctum')->group(function() {
  Route::get('user', [UserController::class, 'find']);
  Route::post('user/password', [UserController::class, 'updatePassword']);

  // Files
  Route::get('files', [FileController::class, 'get']);
  Route::get('files/fetch', [FileController::class, 'fetch']);
  Route::post('file/store', [FileController::class, 'store']);
  Route::delete('file/{file}', [FileController::class, 'destroy']);

  // Upload
  Route::post('image/upload', [UploadController::class, 'image']);
  Route::post('file/upload', [UploadController::class, 'file']);

  // Home
  Route::get('home', [HomeController::class, 'get']);
  Route::get('home/{home}', [HomeController::class, 'find']);
  Route::post('home', [HomeController::class, 'store']);
  Route::put('home/{home}', [HomeController::class, 'update']);
  Route::get('home/state/{home}', [HomeController::class, 'toggle']);
  Route::delete('home/{home}', [HomeController::class, 'destroy']);

  // Home images
  Route::get('home/image/state/{homeImage}', [HomeImageController::class, 'toggle']);
  Route::put('home/image/{homeImage}', [HomeImageController::class, 'coords']);
  Route::post('home/image', [HomeImageController::class, 'store']);
  Route::post('home/image/order', [HomeImageController::class, 'order']);
  Route::delete('home/image/{homeImage}', [HomeImageController::class, 'destroy']);

  // Team images
  Route::get('team/image/state/{teamImage}', [TeamImageController::class, 'toggle']);
  Route::put('team/image/{teamImage}', [TeamImageController::class, 'coords']);
  Route::post('team/image', [TeamImageController::class, 'store']);
  Route::post('team/image/order', [TeamImageController::class, 'order']);
  Route::delete('team/image/{teamImage}', [TeamImageController::class, 'destroy']);

  // Team members
  Route::get('team/members/', [TeamMemberController::class, 'get']);
  Route::get('team/member/{teamMember}', [TeamMemberController::class, 'find']);
  Route::post('team/member/', [TeamMemberController::class, 'store']);
  Route::put('team/member/{teamMember}', [TeamMemberController::class, 'update']);
  Route::post('team/member/order', [TeamMemberController::class, 'order']);
  Route::get('team/member/state/{teamMember}', [TeamMemberController::class, 'toggle']);
  Route::delete('team/member/{teamMember}', [TeamMemberController::class, 'destroy']);

  // Team member images
  Route::get('team/member/image/state/{teamMemberImage}', [TeamMemberImageController::class, 'toggle']);
  Route::put('team/member/image/{teamMemberImage}', [TeamMemberImageController::class, 'coords']);
  Route::post('team/member/image', [TeamMemberImageController::class, 'store']);
  Route::post('team/member/image/order', [TeamMemberImageController::class, 'order']);
  Route::delete('team/member/image/{teamMemberImage}', [TeamMemberImageController::class, 'destroy']);

  // Team
  Route::get('team', [TeamController::class, 'get']);
  Route::get('team/{team}', [TeamController::class, 'find']);
  Route::post('team', [TeamController::class, 'store']);
  Route::put('team/{team}', [TeamController::class, 'update']);
  Route::get('team/state/{team}', [TeamController::class, 'toggle']);
  Route::delete('team/{team}', [TeamController::class, 'destroy']);

  // Publication
  Route::get('publication/{publication}', [PublicationController::class, 'find']);
  Route::post('publication', [PublicationController::class, 'store']);
  Route::put('publication/{publication}', [PublicationController::class, 'update']);
  Route::post('publication/order', [PublicationController::class, 'order']);
  Route::get('publication/state/{publication}', [PublicationController::class, 'toggle']);
  Route::delete('publication/{publication}', [PublicationController::class, 'destroy']);

  // Contact
  Route::get('contact', [ContactController::class, 'get']);
  Route::get('contact/{contact}', [ContactController::class, 'find']);
  Route::post('contact', [ContactController::class, 'store']);
  Route::put('contact/{contact}', [ContactController::class, 'update']);
  Route::get('contact/state/{contact}', [ContactController::class, 'toggle']);
  Route::delete('contact/{contact}', [ContactController::class, 'destroy']);

  // Contact images
  Route::get('contact/image/state/{contactImage}', [ContactImageController::class, 'toggle']);
  Route::put('contact/image/{contactImage}', [ContactImageController::class, 'coords']);
  Route::post('contact/image', [ContactImageController::class, 'store']);
  Route::post('contact/image/order', [ContactImageController::class, 'order']);
  Route::delete('contact/image/{contactImage}', [ContactImageController::class, 'destroy']);

  // Assistant
  Route::get('assistants', [AssistantController::class, 'get']);
  Route::get('assistant/{assistant}', [AssistantController::class, 'find']);
  Route::post('assistant', [AssistantController::class, 'store']);
  Route::put('assistant/{assistant}', [AssistantController::class, 'update']);
  Route::get('assistant/state/{assistant}', [AssistantController::class, 'toggle']);
  Route::delete('assistant/{assistant}', [AssistantController::class, 'destroy']);

  // Assistant images
  Route::get('assistant/image/state/{assistantImage}', [AssistantImageController::class, 'toggle']);
  Route::put('assistant/image/{assistantImage}', [AssistantImageController::class, 'coords']);
  Route::post('assistant/image', [AssistantImageController::class, 'store']);
  Route::post('assistant/image/order', [AssistantImageController::class, 'order']);
  Route::delete('assistant/image/{assistantImage}', [AssistantImageController::class, 'destroy']);
});