<?php

use App\Models\Assistant;
use App\Models\Team;
use App\Models\TeamMember;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'reset'  => false, 'register' => false]);
Route::get('/logout', [LoginController::class, 'logout']);

// password/reset
Route::get('/password/reset', [HomeController::class, 'index'])->name('page.home');

// Home
Route::get('/', [HomeController::class, 'index'])->name('page.home');
Route::get('/de', [HomeController::class, 'index'])->name('page.home');
Route::get('/en', [HomeController::class, 'index'])->name('page.home');
Route::get('/fr', [HomeController::class, 'index'])->name('page.home');
Route::multilingual('/home', [HomeController::class, 'index'])->name('page.home');

// Teams
Route::multilingual('/team-luks', [TeamController::class, 'luks'])->name('page.team.luks');
Route::multilingual('/team-vogt', [TeamController::class, 'vogt'])->name('page.team.vogt');

// Team members
Route::multilingual('/team-{slugTeam}/{slugMember}/{teamMember}', [TeamMemberController::class, 'index'])->name('page.team.member');

// Team assistant
Route::multilingual('/team-vogt/assistant', [AssistantController::class, 'vogt'])->name('page.team.vogt.assistant');
Route::multilingual('/team-luks/assistant', [AssistantController::class, 'luks'])->name('page.team.luks.assistant');

// Contact
Route::multilingual('/contact', [ContactController::class, 'index'])->name('page.contact');

// Url based images
Route::get('/img/original/{filename}', [ImageController::class, 'original']);
Route::get('/img/thumbnail/{filename}', [ImageController::class, 'thumbnail']);
Route::get('/img/crop/{filename}/{maxWidth?}/{maxHeight?}/{coords?}', [ImageController::class, 'crop']);

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