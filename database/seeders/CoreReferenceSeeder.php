<?php

namespace Database\Seeders;

use App\Models\BodyType;
use App\Models\Conversation;
use App\Models\GivenName;
use App\Models\Role;
use App\Models\SportSession;
use Illuminate\Database\Seeder;

class CoreReferenceSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->upsert([
            ['name' => Role::USER, 'created_at' => now(), 'updated_at' => now()],
            ['name' => Role::MODERATOR, 'created_at' => now(), 'updated_at' => now()],
            ['name' => Role::ADMIN, 'created_at' => now(), 'updated_at' => now()],
        ], ['name'], ['updated_at']);

        BodyType::query()->upsert([
            [
                'code' => BodyType::CODE_MALE,
                'label' => 'Corps A',
                'sex' => 'male',
                'image_path' => 'images/perso/body-a.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => BodyType::CODE_FEMALE,
                'label' => 'Corps B',
                'sex' => 'female',
                'image_path' => 'images/perso/body-b.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['code'], ['label', 'sex', 'image_path', 'updated_at']);

        Conversation::query()->updateOrCreate(
            ['key' => 'general'],
            ['name' => 'Général', 'type' => Conversation::TYPE_GENERAL],
        );

        $femaleNames = [
            'Alice', 'Ambre', 'Anaïs', 'Anna', 'Apolline', 'Aurore', 'Ava', 'Camille', 'Capucine', 'Céleste',
            'Charlotte', 'Chloé', 'Clara', 'Clémence', 'Constance', 'Diane', 'Éléonore', 'Élise', 'Ella', 'Emma',
            'Eva', 'Gabrielle', 'Garance', 'Inès', 'Iris', 'Jade', 'Jeanne', 'Joséphine', 'Juliette', 'Léa',
            'Léonie', 'Lila', 'Lina', 'Lisa', 'Louise', 'Lucie', 'Luna', 'Maëlle', 'Manon', 'Margaux',
            'Margot', 'Marie', 'Mathilde', 'Mila', 'Nina', 'Noémie', 'Océane', 'Olivia', 'Pauline', 'Romane',
            'Rose', 'Roxane', 'Salomé', 'Sarah', 'Solène', 'Valentine', 'Victoire', 'Victoria', 'Zoé', 'Adèle',
            'Agathe', 'Alix', 'Amélie', 'Angèle', 'Billie', 'Cassandre', 'Charlie', 'Coline', 'Émilie', 'Faustine',
            'Flavie', 'Héloïse', 'Jade', 'Jasmine', 'Kiara', 'Lana', 'Léna', 'Lily', 'Livia', 'Lou', 'Maëlys',
            'Maya', 'Mélina', 'Mia', 'Naëlle', 'Nora', 'Paloma', 'Pénélope', 'Romy', 'Sasha', 'Sofia',
            'Thaïs', 'Thelma', 'Violette', 'Yasmine', 'Zélie', 'Albane', 'Alma', 'Éden', 'Malia', 'Sixtine',
        ];
        $maleNames = [
            'Adam', 'Adrien', 'Alexandre', 'Alexis', 'Antoine', 'Arthur', 'Auguste', 'Axel', 'Baptiste', 'Bastien',
            'Benjamin', 'Charles', 'Clément', 'Corentin', 'Damien', 'Daniel', 'David', 'Éliott', 'Émile', 'Ethan',
            'Evan', 'Félix', 'Gabriel', 'Gaspard', 'Hugo', 'Isaac', 'Jules', 'Julien', 'Léo', 'Léon',
            'Liam', 'Louis', 'Lucas', 'Malo', 'Martin', 'Mathéo', 'Mathis', 'Mathys', 'Maxence', 'Maxime',
            'Nathan', 'Nicolas', 'Noé', 'Nolan', 'Paul', 'Raphaël', 'Robin', 'Samuel', 'Simon', 'Théo',
            'Thomas', 'Timéo', 'Tristan', 'Victor', 'William', 'Aaron', 'Alban', 'Amaury', 'Ayden', 'Côme',
            'Eden', 'Elio', 'Esteban', 'Gabin', 'Hector', 'Ilan', 'Joachim', 'Lenny', 'Lorenzo', 'Maël',
            'Marceau', 'Marius', 'Matteo', 'Milo', 'Naël', 'Nino', 'Noam', 'Oscar', 'Sacha', 'Tiago',
            'Valentin', 'Achille', 'Augustin', 'Célian', 'César', 'Diego', 'Elias', 'Ezra', 'Léandre', 'Livio',
            'Lucien', 'Marcus', 'Nils', 'Roméo', 'Solal', 'Théodore', 'Tom', 'Ulysse', 'Vadim', 'Zacharie',
        ];
        $givenNameRows = collect([
            'female' => array_values(array_unique($femaleNames)),
            'male' => array_values(array_unique($maleNames)),
        ])->flatMap(fn (array $names, string $sex) => collect($names)->map(fn (string $name) => [
            'name' => $name,
            'sex' => $sex,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->values()->all();

        GivenName::query()->upsert(
            $givenNameRows,
            ['name', 'sex'],
            ['is_active', 'updated_at'],
        );

        SportSession::query()->upsert([
            [
                'name' => 'Séance à l’unité',
                'type' => 'single',
                'price' => 40,
                'duration_days' => 1,
                'physical_condition_effect' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'premium',
                'type' => 'gym',
                'price' => 100,
                'duration_days' => 1,
                'physical_condition_effect' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'standard',
                'type' => 'gym',
                'price' => 130,
                'duration_days' => 3,
                'physical_condition_effect' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'basic',
                'type' => 'gym',
                'price' => 200,
                'duration_days' => 7,
                'physical_condition_effect' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['name'], [
            'type',
            'price',
            'duration_days',
            'physical_condition_effect',
            'updated_at',
        ]);
    }
}
