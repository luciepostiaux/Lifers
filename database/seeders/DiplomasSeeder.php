<?php

namespace Database\Seeders;

use App\Models\Diploma;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiplomasSeeder extends Seeder
{
    public function run(): void
    {
        Diploma::insert([
            ['name' => 'Année Générale en Sciences de la Santé', 'description' => 'Félicitations pour l\'obtention de ton diplôme en Année Générale en Sciences de la Santé ! Tu as désormais une base solide dans les principes fondamentaux de la santé humaine, te préparant à poursuivre des études spécialisées ou à entrer dans le domaine de la santé avec une compréhension approfondie.'],
            ['name' => 'Spécialisation en Médecine Générale', 'description' => 'Bravo pour avoir obtenu ta spécialisation en Médecine Générale ! Tu es maintenant un médecin généraliste compétent, prêt à fournir des soins complets et holistiques à tes patients, tout en étant leur premier point de contact pour les problèmes de santé.'],
            ['name' => 'Spécialisation en Chirurgie', 'description' => "Félicitations pour ta spécialisation en Chirurgie ! Tu as acquis les compétences techniques et la précision nécessaires pour pratiquer la chirurgie avec succès, sauvant des vies et améliorant la qualité de vie des patients grâce à ton expertise."],
            ['name' => 'Spécialisation en Psychiatrie', 'description' => "Bravo pour avoir terminé ta spécialisation en Psychiatrie ! Tu es maintenant un expert dans l'évaluation, le diagnostic et le traitement des troubles mentaux, offrant un soutien crucial à ceux qui en ont besoin et contribuant à l'amélioration de leur bien-être mental."],
            ['name' => 'Spécialisation en Soins Infirmiers', 'description' => "Félicitations pour ton diplôme en Spécialisation en Soins Infirmiers ! En tant qu'infirmier qualifié, tu es le pilier du système de santé, fournissant des soins attentionnés et compétents à tes patients tout en apportant confort et soutien à leurs familles."],
            ['name' => 'Spécialisation en Pharmacologie', 'description' => 'Bravo pour avoir complété ta spécialisation en Pharmacologie ! Tu es maintenant un expert dans l\'étude des médicaments et de leur impact sur le corps humain, contribuant à l\'avancement de la science médicale et à l\'amélioration des traitements disponibles pour les patients.'],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],
            // ['name' => '', 'description' => ''],

        ]);
    }
}
