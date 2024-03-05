<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventsSeeder extends Seeder
{
    public function run()
    {
        $events = [
            [
                'name' => 'Noël',
                'description' => 'Célébration de Noël avec des cadeaux et du bon temps en famille.',
                'start_time' => Carbon::create(2024, 12, 1, 0, 0, 0),
                'end_time' => Carbon::create(2024, 12, 25, 23, 59, 59),
            ],
            [
                'name' => 'Nouvel An',
                'description' => 'Fête pour célébrer l\'arrivée de la nouvelle année.',
                'start_time' => Carbon::create(2025, 1, 1, 0, 0, 0),
                'end_time' => Carbon::create(2025, 1, 14, 23, 59, 59),
            ],
            // Ajoutez d'autres événements ici...
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
