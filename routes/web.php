<?php

use App\Models\Assistant;
use App\Models\Team;
use App\Models\TeamMember;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'reset'  => false, 'register' => false]);
Route::get('/logout', 'Auth\LoginController@logout');

// password/reset
Route::get('/password/reset', 'HomeController@index')->name('page.home');

// Home
Route::get('/', 'HomeController@index')->name('page.home');
Route::get('/de', 'HomeController@index')->name('page.home');
Route::get('/en', 'HomeController@index')->name('page.home');
Route::get('/fr', 'HomeController@index')->name('page.home');
Route::multilingual('/home', 'HomeController@index')->name('page.home');

// Teams
Route::multilingual('/team-luks', 'TeamController@luks')->name('page.team.luks');
Route::multilingual('/team-vogt', 'TeamController@vogt')->name('page.team.vogt');

// Team members
Route::multilingual('/team-{slugTeam}/{slugMember}/{teamMember}', 'TeamMemberController@index')->name('page.team.member');

// Team assistant
Route::multilingual('/team-vogt/assistant', 'AssistantController@vogt')->name('page.team.vogt.assistant');
Route::multilingual('/team-luks/assistant', 'AssistantController@luks')->name('page.team.luks.assistant');



// Contact
Route::multilingual('/contact', 'ContactController@index')->name('page.contact');

// Url based images
Route::get('/img/{template}/{filename}', 'ImageController@getResponse');

/*
|--------------------------------------------------------------------------
| Admin Web routes
|--------------------------------------------------------------------------
|
*/

Route::middleware('auth:sanctum', 'verified')->group(function() {
  Route::get('administration/{any?}', function () {
    return view('dashboards.administration.app');
  })->where('any', '.*')->middleware('role:admin')->name('dashboard_admin');
});
