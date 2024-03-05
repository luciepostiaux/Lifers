<?php

namespace Database\Seeders;

use App\Models\Perso;
use App\Models\PersoStudyEnrollment;
use App\Models\Study;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PersoStudyEnrollmentsSeeder extends Seeder
{
    /**
     * Exécute les seeds de la base de données.
     */
    public function run(): void
    {
        $studies = Study::all();
        $personnages = Perso::all();

        $personnages->each(function ($perso) use ($studies) {
            $study = $studies->random();

            $end_date = Carbon::now()->addDays($study->duration);

            PersoStudyEnrollment::create([
                'perso_id' => $perso->id,
                'study_id' => $study->id,
                'end_date' => $end_date,
            ]);
        });
    }
}
