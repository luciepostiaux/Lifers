<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AtHomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiplomaController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LifeGaugesController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ProfilPersoController;
use App\Http\Controllers\SicknessController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\SubscriptionController;
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
    Route::get('/character/create', [CharacterController::class, 'create'])->name('character.create');
    Route::post('/character/store', [CharacterController::class, 'store'])->name('character.store');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ensure-perso',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfilPersoController::class, 'index'])->name('profil');
    Route::get('/athome', [AtHomeController::class, 'index'])->name('athome');
    Route::post('/consume-item', [AtHomeController::class, 'consumeItem'])->name('consume-item');

    Route::get('/study', [StudyController::class, 'index'])->name('study.index');
    Route::get('/study/current/{id}', [StudyController::class, 'showCurrentStudy'])->name('study.current.show');
    Route::post('/study/resign', [StudyController::class, 'resign'])->name('study.resign');
    Route::post('/study/{study}/claimDiploma', [StudyController::class, 'claimDiploma'])->name('study.claimDiploma');
    Route::post('/study/enroll/{studyId}', [StudyController::class, 'enroll'])->name('study.enroll');
    Route::post('/study/drop', [StudyController::class, 'dropCurrentStudy'])->name('study.drop');

    Route::get('/job', [JobController::class, 'index'])->name('job');
    Route::post('/job/apply/{jobId}', [JobController::class, 'apply'])->name('job.apply');
    Route::get('/job/current/{id}', [JobController::class, 'showCurrentJob'])->name('job.current.show');
    Route::post('/job/resign', [JobController::class, 'resign'])->name('job.resign');
    Route::post('/job/change/{newJobId}', [JobController::class, 'changeJob'])->name('job.change');

    Route::get('/city', [CityController::class, 'index'])->name('city');

    Route::get('/city/lifemarket', [CityController::class, 'lifeMarket'])->name('city.entertainment');
    Route::get('/city/entertainment', [CityController::class, 'entertainment'])->name('city.entertainment');
    Route::get('/city/doctor', [CityController::class, 'doctor'])->name('doctor.index');
    Route::post('/doctor/treat', [SicknessController::class, 'treatSickness'])->name('doctor.treat');


    Route::get('/city/sport', [CityController::class, 'sport'])->name('city.sport');
    Route::post('/city/buy-single-sport-session', [CityController::class, 'buySingleSportSession'])->name('city.buySingleSportSession');
    Route::post('/city/subscribe-to-gym', [SubscriptionController::class, 'subscribeToGym'])->name('city.subscribeToGym');
    Route::post('/city/cancel-gym-subscription', [SubscriptionController::class, 'cancelGymSubscription'])->name('city.cancelGymSubscription');
    Route::post('/city/participate', [CityController::class, 'participateInActivity'])->name('city.participate');

    Route::post('/purchase', [CartController::class, 'purchase'])->name('purchase');




    Route::get('/mail', [MailController::class, 'index'])->name('mail');

    Route::get('/social', [SocialController::class, 'index'])->name('social');
    Route::get('/life-gauges', [LifeGaugesController::class, 'index'])->name('life-gauges.index');
    // Route::put('/life-gauges/{perso}', [LifeGaugesController::class, 'update'])->name('life-gauges.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/grant-diploma', [AdminController::class, 'grantDiploma'])->name('admin.grantDiploma');
    Route::post('/admin/remove-diploma', [AdminController::class, 'removeDiploma'])->name('admin.removeDiploma');
});
