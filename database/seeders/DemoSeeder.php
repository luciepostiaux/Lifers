<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityEffect;
use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\BodyType;
use App\Models\Conversation;
use App\Models\Diploma;
use App\Models\Event;
use App\Models\Friendship;
use App\Models\Item;
use App\Models\Job;
use App\Models\JobAction;
use App\Models\Lifer;
use App\Models\LiferEmployment;
use App\Models\LiferProfile;
use App\Models\LiferStudyEnrollment;
use App\Models\LiferSubscription;
use App\Models\Message;
use App\Models\Place;
use App\Models\ProfileComment;
use App\Models\Rewind;
use App\Models\Role;
use App\Models\Sickness;
use App\Models\SportSession;
use App\Models\Study;
use App\Models\Suggestion;
use App\Models\User;
use App\Services\LiferLifecycleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoreReferenceSeeder::class);
        $this->call(EducationCareerCatalogSeeder::class);
        $this->call(ItemCatalogSeeder::class);
        $this->call(SicknessCatalogSeeder::class);

        $email = env('LIFERS_DEMO_EMAIL');
        $password = env('LIFERS_DEMO_PASSWORD');

        if (! $email || ! $password) {
            throw new RuntimeException('Les identifiants du compte de démonstration sont absents de l’environnement local.');
        }

        $mainUser = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Compte de démonstration',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'consentement_rgpd' => true,
            ],
        );
        $otherUser = User::query()->updateOrCreate(
            ['email' => 'alex.demo@lifers.local'],
            [
                'name' => 'Compte secondaire de démonstration',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'consentement_rgpd' => true,
            ],
        );

        $userRole = Role::where('name', Role::USER)->firstOrFail();
        $mainUser->roles()->syncWithoutDetaching($userRole->id);
        $otherUser->roles()->syncWithoutDetaching($userRole->id);

        $mainLifer = $this->activeLifer(
            $mainUser,
            BodyType::where('code', 'A')->firstOrFail(),
            'Camille',
            'Démo',
        );
        $otherLifer = $this->activeLifer(
            $otherUser,
            BodyType::where('code', 'B')->firstOrFail(),
            'Alex',
            'Rivière',
        );

        Lifer::query()->firstOrCreate([
            'user_id' => $mainUser->id,
            'first_name' => 'Charlie',
            'last_name' => 'Souvenir',
            'status' => Lifer::STATUS_DEAD,
        ], [
            'sex' => Lifer::SEX_FEMALE,
            'born_at' => now()->subDays(48),
            'died_at' => now()->subDays(3),
            'age_at_death' => 33,
            'death_cause' => 'Vieillesse — donnée de démonstration',
        ]);

        $mainLifer->gameState()->update(['money' => 1250]);
        $mainLifer->lifeGauge()->update([
            'hunger' => 72,
            'thirst' => 48,
            'clean' => 81,
            'happiness' => 67,
            'entertainment' => 54,
            'physical_condition' => 76,
            'health' => 88,
        ]);
        $otherLifer->gameState()->update(['money' => 830]);
        $otherLifer->lifeGauge()->update([
            'hunger' => 90,
            'thirst' => 85,
            'clean' => 70,
            'happiness' => 92,
            'entertainment' => 78,
            'physical_condition' => 64,
            'health' => 95,
        ]);

        $university = Place::query()->updateOrCreate(
            ['name' => 'Université de Lifers'],
            ['image_path' => '/images/places/universite.png'],
        );
        $hospital = Place::query()->updateOrCreate(
            ['name' => 'Hôpital de Lifers'],
            ['image_path' => '/images/places/hopital.png'],
        );
        $employmentOffice = Place::query()->updateOrCreate(
            ['name' => 'Maison de l’emploi'],
            ['image_path' => '/images/places/emploi.png'],
        );

        $firstAidDiploma = Diploma::where('name', 'Année Générale en Sciences de la Santé')->firstOrFail();
        $nursingDiploma = Diploma::where('name', 'Spécialisation en Soins Infirmiers')->firstOrFail();
        $mainLifer->diplomas()->syncWithoutDetaching([
            $firstAidDiploma->id => ['earned_at' => now()->subDays(5)],
        ]);

        $assistantJob = Job::where('name', 'Artisan')->firstOrFail();
        $nurseJob = Job::where('name', 'Infirmier / Infirmière')->firstOrFail();
        JobAction::query()->updateOrCreate(
            ['job_id' => $assistantJob->id, 'name' => 'Aider un voisin — démo'],
            ['description' => 'Action de démonstration.', 'amount' => 15, 'success_chance' => 90],
        );
        LiferEmployment::query()->updateOrCreate(
            ['lifer_id' => $mainLifer->id],
            ['job_id' => $assistantJob->id, 'started_at' => now()->subDays(2)],
        );

        $nursingStudy = Study::where('name', 'Spécialisation en Soins Infirmiers')->firstOrFail();
        LiferStudyEnrollment::query()->updateOrCreate(
            ['lifer_id' => $mainLifer->id, 'status' => LiferStudyEnrollment::STATUS_ACTIVE],
            [
                'study_id' => $nursingStudy->id,
                'started_at' => now()->subDay(),
                'ends_at' => now()->addDays(2),
                'ended_at' => null,
            ],
        );

        $apple = Item::where('name', 'Pomme')->firstOrFail();
        $water = Item::where('name', 'Eau')->firstOrFail();
        $soap = Item::where('name', 'Savon')->firstOrFail();
        foreach ([$apple->id => 3, $water->id => 2, $soap->id => 1] as $itemId => $quantity) {
            DB::table('inventory_items')->updateOrInsert(
                ['inventory_id' => $mainLifer->id, 'item_id' => $itemId],
                ['quantity' => $quantity, 'created_at' => now(), 'updated_at' => now()],
            );
        }

        $cinema = Activity::query()->updateOrCreate(
            ['name' => 'Séance de cinéma — démo'],
            ['description' => 'Une activité de démonstration pour améliorer le divertissement.', 'price' => 45, 'category' => 'Culture'],
        );
        ActivityEffect::query()->updateOrCreate(
            ['activity_id' => $cinema->id, 'gauge' => 'entertainment'],
            ['effect' => 24],
        );
        $park = Activity::query()->updateOrCreate(
            ['name' => 'Promenade au parc — démo'],
            ['description' => 'Une activité de démonstration pour le bonheur et la condition physique.', 'price' => 20, 'category' => 'Plein air'],
        );
        ActivityEffect::query()->updateOrCreate(
            ['activity_id' => $park->id, 'gauge' => 'happiness'],
            ['effect' => 15],
        );
        ActivityEffect::query()->updateOrCreate(
            ['activity_id' => $park->id, 'gauge' => 'physical_condition'],
            ['effect' => 8],
        );

        $cold = Sickness::query()->where('slug', 'rhume')->firstOrFail();
        $mainLifer->sicknesses()->syncWithoutDetaching([
            $cold->id => [
                'contracted_at' => now()->subHours(6),
                'expected_recovery_at' => now()->addDays(2),
                'last_effect_applied_on' => today(),
                'fatal_at' => null,
                'created_at' => now()->subHours(6),
                'updated_at' => now(),
            ],
        ]);

        $cat = AnimalType::query()->updateOrCreate(
            ['name' => 'Chat — démo'],
            ['description' => 'Type d’animal de démonstration.'],
        );
        Animal::query()->updateOrCreate(
            ['lifer_id' => $mainLifer->id, 'name' => 'Pixel'],
            ['animal_type_id' => $cat->id, 'born_at' => now()->subDays(9), 'is_alive' => true, 'died_at' => null],
        );

        $basicPlan = SportSession::where('name', 'basic')->firstOrFail();
        LiferSubscription::query()->updateOrCreate(
            ['lifer_id' => $mainLifer->id, 'status' => 'active'],
            [
                'sport_session_id' => $basicPlan->id,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(6),
            ],
        );

        $general = Conversation::where('key', 'general')->firstOrFail();
        $general->lifers()->syncWithoutDetaching([$mainLifer->id, $otherLifer->id]);
        $private = Conversation::query()->updateOrCreate(
            ['key' => Conversation::privateKey($mainLifer->id, $otherLifer->id)],
            ['name' => null, 'type' => Conversation::TYPE_PRIVATE],
        );
        $private->lifers()->syncWithoutDetaching([$mainLifer->id, $otherLifer->id]);

        Message::query()->firstOrCreate([
            'conversation_id' => $general->id,
            'sender_lifer_id' => $otherLifer->id,
            'content' => 'Bienvenue dans le salon général de démonstration !',
        ]);
        Message::query()->firstOrCreate([
            'conversation_id' => $private->id,
            'sender_lifer_id' => $otherLifer->id,
            'content' => 'Ceci est une conversation privée de démonstration.',
        ]);

        Friendship::query()->updateOrCreate(
            ['requester_lifer_id' => $otherLifer->id, 'recipient_lifer_id' => $mainLifer->id],
            ['status' => Friendship::STATUS_ACCEPTED],
        );
        ProfileComment::query()->updateOrCreate(
            [
                'author_lifer_id' => $otherLifer->id,
                'receiver_lifer_id' => $mainLifer->id,
                'content' => 'Profil de démonstration prêt à être exploré.',
            ],
            [
                'status' => ProfileComment::STATUS_APPROVED,
                'moderated_at' => now(),
            ],
        );
        LiferProfile::query()->updateOrCreate(
            ['lifer_id' => $mainLifer->id],
            [
                'show_money' => false,
                'content' => [
                    'type' => 'doc',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [[
                            'type' => 'text',
                            'text' => 'Bienvenue sur le profil de démonstration de mon Lifer.',
                        ]],
                    ]],
                ],
            ],
        );
        Suggestion::query()->firstOrCreate([
            'lifer_id' => $mainLifer->id,
            'content' => 'Suggestion créée uniquement pour remplir la base de démonstration.',
            'status' => 'pending',
        ]);
        $event = Event::query()->updateOrCreate(
            ['name' => 'Rencontre communautaire — démo'],
            [
                'description' => 'Événement de démonstration non définitif.',
                'starts_at' => now()->addDays(2),
                'ends_at' => now()->addDays(2)->addHours(2),
            ],
        );
        $event->lifers()->syncWithoutDetaching([$mainLifer->id, $otherLifer->id]);
        $rewind = Rewind::query()->firstOrCreate(
            ['price' => 300],
            ['image_path' => null],
        );
        $mainLifer->belongsToMany(Rewind::class, 'lifer_rewind')->syncWithoutDetaching($rewind->id);

        $this->command?->info("Démonstration prête pour {$email}.");
    }

    private function activeLifer(User $user, BodyType $bodyType, string $firstName, string $lastName): Lifer
    {
        $existing = $user->activeLifer()->first();

        if ($existing) {
            return $existing;
        }

        return app(LiferLifecycleService::class)->create($user, $bodyType, [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }
}
