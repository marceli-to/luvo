<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'register' => false]);
Route::get('/logout', 'Auth\LoginController@logout');

// Home
Route::get('/', 'HomeController@index')->name('page.home');
Route::get('/en', 'HomeController@index')->name('page.home');
Route::get('/de', 'HomeController@index')->name('page.home');

// News
Route::multilingual('/news', 'NewsController@index')->name('page.news.listing');
Route::multilingual('/news/{slug?}/{news}', 'NewsController@show')->name('page.news.show');

// Url based images
Route::get('/img/{template}/{filename}', 'ImageController@getResponse');


/*
|--------------------------------------------------------------------------
| Admin Web routes
|--------------------------------------------------------------------------
|
*/

Route::middleware('auth:sanctum', 'verified')->group(function() {

  // CatchAll: Dashboard Administration
  Route::get('administration/{any?}', function () {
    return view('dashboards.administration.app');
  })->where('any', '.*')->middleware('role:admin')->name('dashboard_admin');

});
