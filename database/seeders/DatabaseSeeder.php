<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GendersSeeder::class,
            HairstylesSeeder::class,
            DiplomasSeeder::class,
            PlacesSeeder::class,
            AnimalsTypesSeeder::class,
            ColorsSeeder::class,
            SicknessSeeder::class,
            SicknessGaugesSeeder::class,
            UsersSeeder::class,
            ItemsSeeder::class,
            OutfitTopSeeder::class,
            OutfitBottomSeeder::class,
            OutfitShoeSeeder::class,
            JobsSeeder::class,
            StudiesSeeder::class,
            PersoBodysSeeder::class,
            PersoSeeder::class,
            LifeGaugesSeeder::class,
            JobsActionsSeeder::class,
            InventoriesSeeder::class,
            PersoDiplomasSeeder::class,
            InventoriesOutfitsTopsSeeder::class,
            InventoriesOutfitsBottomsSeeder::class,
            InventoriesOutfitsShoesSeeder::class,
            InventoryItemSeeder::class,
            SicknessSicknessGaugesSeeder::class,
            PersoSicknessSeeder::class,
            FriendshipsSeeder::class,
            EventsUsersSeeder::class,
            RewindsUsersSeeder::class,
            EventsSeeder::class,
            RewindsSeeder::class,
            SuggestionsSeeder::class,
            ItemEffectsSeeder::class,
            PersoStudyEnrollmentsSeeder::class,
            RoleSeeder::class,

        ]);
    }
}
