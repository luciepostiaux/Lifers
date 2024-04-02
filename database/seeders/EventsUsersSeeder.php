<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventsUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les événements et utilisateurs
        $events = Event::all();
        $users = User::all();

        // S'assurer qu'il y a des événements et des utilisateurs
        if ($events->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Associer chaque événement à 3 utilisateurs aléatoires
        $events->each(function ($event) use ($users) {
            $eventUsers = $users->random(min(3, $users->count()))->pluck('id');
            $event->users()->attach($eventUsers);
        });
    }
}
