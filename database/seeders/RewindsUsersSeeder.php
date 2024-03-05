<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rewind;

class RewindsUsersSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les rewinds et utilisateurs
        $rewinds = Rewind::all();
        $users = User::all();

        // Vérifier qu'il y a des rewinds et des utilisateurs
        if ($rewinds->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Pour chaque rewind, associer aléatoirement jusqu'à 3 utilisateurs
        $rewinds->each(function ($rewind) use ($users) {
            $rewindUsers = $users->random(min(3, $users->count()))->pluck('id');
            $rewind->users()->attach($rewindUsers);
        });
    }
}
