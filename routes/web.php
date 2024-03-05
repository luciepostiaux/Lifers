<?php

use App\Http\Controllers\AtHomeController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ProfilPersoController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StudyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfilPersoController::class, 'index'])->name('profil');
    Route::get('/athome', [AtHomeController::class, 'index'])->name('athome');

    Route::get('/study', [StudyController::class, 'index'])->name('study');
    Route::get('/study/current', [StudyController::class, 'currentStudy'])->name('study.current');


    Route::get('/job', [JobController::class, 'index'])->name('job');

    Route::get('/city', [CityController::class, 'index'])->name('city');

    Route::get('/mail', [MailController::class, 'index'])->name('mail');
    Route::get('/social', [SocialController::class, 'index'])->name('social');
});
