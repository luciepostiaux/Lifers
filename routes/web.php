<?php

use App\Http\Controllers\AdminBanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLiferController;
use App\Http\Controllers\AtHomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DailyJournalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LifeGaugesController;
use App\Http\Controllers\LiferProfileImageController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\OrphanageController;
use App\Http\Controllers\ProfileCommentController;
use App\Http\Controllers\ProfilPersoController;
use App\Http\Controllers\SicknessController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\SubscriptionController;
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
    $title = 'Lifers — Ta seconde vie commence ici';
    $description = 'Lifers est un jeu de simulation de vie communautaire : crée ton Lifer, développe sa carrière, sa famille et ses relations dans une ville vivante.';

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'seo' => [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => url('/'),
            'socialImageUrl' => url('/images/landing/hero-lifers.png'),
        ],
    ]);
})->name('home');

Route::get('/sitemap.xml', function () {
    $homeUrl = htmlspecialchars(url('/'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{$homeUrl}</loc>
    </url>
</urlset>
XML;

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::post('/session/keep-alive', fn () => response()->noContent())
    ->middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
    ])
    ->name('session.keep-alive');

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
    'ensure-lifer',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/family', [FamilyController::class, 'index'])->name('family.index');
    Route::post('/family/requests', [FamilyController::class, 'storeRequest'])->name('family.requests.store');
    Route::patch('/family/requests/{familyRequest}', [FamilyController::class, 'respond'])->name('family.requests.respond');
    Route::delete('/family/requests/{familyRequest}', [FamilyController::class, 'cancelRequest'])->name('family.requests.cancel');
    Route::delete('/family/marriage', [FamilyController::class, 'divorce'])->name('family.marriage.divorce');
    Route::post('/family/favorites/{favoriteLifer}', [FamilyController::class, 'storeFavorite'])->name('family.favorites.store');
    Route::delete('/family/favorites/{favoriteLifer}', [FamilyController::class, 'destroyFavorite'])->name('family.favorites.destroy');
    Route::patch('/family/pregnancies/{pregnancy}/children/{child}', [FamilyController::class, 'nameExpectedChild'])->name('family.children.name');
    Route::post('/family/children/care-all', [FamilyController::class, 'careForAllChildren'])->name('family.children.care-all');
    Route::post('/family/children/{child}/care', [FamilyController::class, 'careForChild'])->name('family.children.care');
    Route::post('/family/children/{child}/renounce', [FamilyController::class, 'renounceChild'])->name('family.children.renounce');
    Route::post('/family/children/{child}/abandon', [FamilyController::class, 'abandonChild'])->name('family.children.abandon');

    Route::get('/profil', [ProfilPersoController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfilPersoController::class, 'update'])->name('profil.update');
    Route::post('/profil/images', [LiferProfileImageController::class, 'store'])
        ->middleware('throttle:media-upload')
        ->name('profil.images.store');
    Route::delete('/profil/images/{image}', [LiferProfileImageController::class, 'destroy'])->name('profil.images.destroy');
    Route::get('/lifers/{lifer}/profil', [ProfilPersoController::class, 'show'])->name('lifers.profile.show');
    Route::post('/lifers/{lifer}/profil/comments', [ProfileCommentController::class, 'store'])
        ->middleware('throttle:community-write')
        ->name('lifers.profile.comments.store');
    Route::patch('/profil/comments/{comment}/approve', [ProfileCommentController::class, 'approve'])->name('profil.comments.approve');
    Route::delete('/profil/comments/{comment}', [ProfileCommentController::class, 'destroy'])->name('profil.comments.destroy');
    Route::get('/athome', [AtHomeController::class, 'index'])->name('athome');
    Route::post('/consume-item', [AtHomeController::class, 'consumeItem'])->name('consume-item');

    Route::get('/study', [StudyController::class, 'index'])->name('study.index');
    Route::get('/study/current/{id}', [StudyController::class, 'showCurrentStudy'])->name('study.current.show');
    Route::post('/study/resign', [StudyController::class, 'resign'])->name('study.resign');
    Route::post('/study/{study}/claimDiploma', [StudyController::class, 'claimDiploma'])->name('study.claimDiploma');
    Route::post('/study/enroll/{studyId}', [StudyController::class, 'enroll'])->name('study.enroll');
    Route::post('/study/drop', [StudyController::class, 'resign'])->name('study.drop');

    Route::get('/job', [JobController::class, 'index'])->name('job');
    Route::post('/job/apply/{jobId}', [JobController::class, 'apply'])->name('job.apply');
    Route::get('/job/current/{id}', [JobController::class, 'showCurrentJob'])->name('job.current.show');
    Route::post('/job/resign', [JobController::class, 'resign'])->name('job.resign');
    Route::post('/job/change/{newJobId}', [JobController::class, 'changeJob'])->name('job.change');

    Route::get('/city', [CityController::class, 'index'])->name('city');
    Route::get('/city/journal', [DailyJournalController::class, 'index'])->name('city.journal.index');
    Route::post('/city/journal/purchase', [DailyJournalController::class, 'purchase'])->name('city.journal.purchase');
    Route::get('/city/orphanage', [OrphanageController::class, 'index'])->name('city.orphanage');
    Route::post('/city/orphanage/{child}/adopt', [OrphanageController::class, 'adopt'])->name('city.orphanage.adopt');

    Route::get('/city/lifemarket', [CityController::class, 'lifeMarket'])->name('city.lifemarket');
    Route::get('/city/entertainment', [CityController::class, 'entertainment'])->name('city.entertainment');
    Route::get('/city/doctor', [CityController::class, 'doctor'])->name('doctor.index');
    Route::post('/treat-sickness', [SicknessController::class, 'treatSickness'])->name('treat-sickness');
    Route::post('/visit-doctor', [SicknessController::class, 'visitDoctor'])->name('visit-doctor');

    Route::get('/city/sport', [CityController::class, 'sport'])->name('city.sport');
    Route::post('/city/buy-single-sport-session', [CityController::class, 'buySingleSportSession'])->name('city.buySingleSportSession');
    Route::post('/city/subscribe-to-gym', [SubscriptionController::class, 'subscribeToGym'])->name('city.subscribeToGym');
    Route::post('/city/cancel-gym-subscription', [SubscriptionController::class, 'cancelGymSubscription'])->name('city.cancelGymSubscription');
    Route::post('/city/participate', [CityController::class, 'participateInActivity'])->name('city.participate');

    Route::post('/purchase', [CartController::class, 'purchase'])->name('purchase');

    Route::get('/mail', [MailController::class, 'index'])->name('mail');

    Route::get('/social/{id?}', [SocialController::class, 'index'])->name('social');

    Route::get('/conversations', [ConversationController::class, 'index']);

    Route::post('/conversations', [ConversationController::class, 'store'])
        ->middleware('throttle:community-write');
    Route::post('/conversations/groups', [ConversationController::class, 'storeGroup'])
        ->middleware('throttle:community-write')
        ->name('conversations.groups.store');

    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);

    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'fetchMessages']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])
        ->middleware('throttle:community-write');
    Route::post('/conversations/{conversation}/read', [ConversationController::class, 'markRead'])->name('conversations.read');
    Route::post('/conversations/{conversation}/members', [ConversationController::class, 'addMembers'])
        ->middleware('throttle:community-write')
        ->name('conversations.members.store');
    Route::delete('/conversations/{conversation}/members/me', [ConversationController::class, 'leaveGroup'])->name('conversations.groups.leave');

    Route::get('/life-gauges', [LifeGaugesController::class, 'index'])->name('life-gauges.index');
    // Route::put('/life-gauges/{perso}', [LifeGaugesController::class, 'update'])->name('life-gauges.update');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'admin',
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role.update');
    Route::post('/bans', [AdminBanController::class, 'store'])->name('bans.store');
    Route::delete('/bans/{ban}', [AdminBanController::class, 'destroy'])->name('bans.destroy');
    Route::get('/lifers/{lifer}', [AdminLiferController::class, 'show'])->name('lifers.show');
    Route::patch('/lifers/{lifer}/money', [AdminLiferController::class, 'updateMoney'])->name('lifers.money.update');
    Route::patch('/lifers/{lifer}/gauges', [AdminLiferController::class, 'updateGauges'])->name('lifers.gauges.update');
    Route::post('/lifers/{lifer}/sicknesses', [AdminLiferController::class, 'addSickness'])->name('lifers.sicknesses.store');
    Route::delete('/lifers/{lifer}/sicknesses/{sickness}', [AdminLiferController::class, 'removeSickness'])->name('lifers.sicknesses.destroy');
    Route::post('/lifers/{lifer}/kill', [AdminLiferController::class, 'kill'])->name('lifers.kill');
    Route::post('/grant-diploma', [AdminController::class, 'grantDiploma'])->name('grantDiploma');
    Route::post('/remove-diploma', [AdminController::class, 'removeDiploma'])->name('removeDiploma');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ensure-lifer',
    'can:moderate',
])->prefix('moderation')->name('moderation.')->group(function () {
    Route::get('/', [ModerationController::class, 'index'])->name('dashboard');
    Route::patch('/lifers/{lifer}/profile', [ModerationController::class, 'updateProfile'])->name('profiles.update');
    Route::delete('/profile-images/{image}', [ModerationController::class, 'destroyProfileImage'])->name('profile-images.destroy');
    Route::delete('/comments/{comment}', [ModerationController::class, 'destroyComment'])->name('comments.destroy');
    Route::delete('/messages/{message}', [ModerationController::class, 'destroyMessage'])->name('messages.destroy');
});
