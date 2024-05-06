<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AtHomeController;
use App\Http\Controllers\BugIdeaController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiplomaController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LifeGaugesController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfilPersoController;
use App\Http\Controllers\ResidencePaymentController;
use App\Http\Controllers\SicknessController;
use App\Http\Controllers\SmsMessageController;
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
})->name('welcome');
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/character/create', [CharacterController::class, 'create'])->name('character.create');
    Route::post('/character/store', [CharacterController::class, 'store'])->name('character.store');
});

Route::get('/perso/{perso}', [ProfilPersoController::class, 'public'])->name('profil.public');
Route::get('/mentions-legales', function () {
    return Inertia::render('LegalMentions');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ensure-perso',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/aides-informations', function () {
        return Inertia::render('HelpInfo');
    })->name('help-info');

    Route::get('/profil', [ProfilPersoController::class, 'index'])->name('profil');
    Route::post('/profil/description', [ProfilPersoController::class, 'saveDescription'])->name('profil.description');
    Route::post('/send-friend-request', [FriendshipController::class, 'sendRequest']);
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');


    Route::get('/athome', [AtHomeController::class, 'index'])->name('athome');
    Route::post('/consume-item', [AtHomeController::class, 'consumeItem'])->name('consume-item');
    Route::post('/residences/{id}/sell', [AtHomeController::class, 'sellResidence'])->name('residences.sell');
    Route::put('/residences/set-active/{id}', [AtHomeController::class, 'setActive'])->name('residences.update');

    Route::get('/family', [FamilyController::class, 'index'])->name('family');

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
    Route::post('/treat-sickness', [SicknessController::class, 'treatSickness'])->name('treat-sickness');
    Route::post('/visit-doctor', [SicknessController::class, 'visitDoctor'])->name('visit-doctor');

    Route::get('/city/housing', [CityController::class, 'residence'])->name('housing.index');
    Route::post('/buy/residence', [ResidencePaymentController::class, 'buy'])->name('buy.residence');


    Route::get('/city/sport', [CityController::class, 'sport'])->name('city.sport');
    Route::post('/city/buy-single-sport-session', [CityController::class, 'buySingleSportSession'])->name('city.buySingleSportSession');
    Route::post('/city/subscribe-to-gym', [SubscriptionController::class, 'subscribeToGym'])->name('city.subscribeToGym');
    Route::post('/city/cancel-gym-subscription', [SubscriptionController::class, 'cancelGymSubscription'])->name('city.cancelGymSubscription');
    Route::post('/city/participate', [CityController::class, 'participateInActivity'])->name('city.participate');

    Route::post('/purchase', [CartController::class, 'purchase'])->name('purchase');




    Route::get('/gsm', [SmsMessageController::class, 'index'])->name('gsm.index');
    Route::get('/gsm/new', [SmsMessageController::class, 'create'])->name('gsm.create');

    Route::get('/social/{id?}', [SocialController::class, 'index'])->name('social');


    Route::get('/conversations', [ConversationController::class, 'index']);

    Route::post('/conversations', [ConversationController::class, 'store']);

    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);

    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'fetchMessages']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage']);

    Route::get('/bugidea', [BugIdeaController::class, 'index'])->name('bugidea.index');
    Route::get('/suggestions/{id}', [BugIdeaController::class, 'showSuggestion'])->name('suggestions.show');
    Route::get('/bugs/{id}', [BugIdeaController::class, 'showBug'])->name('bugs.show');

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
