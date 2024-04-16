<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobsSeeder extends Seeder
{
    public function run()
    {
        Job::insert([
            [
                'name' => 'Médecin Généraliste',
                'description_1' => "Première ligne de défense contre la maladie, offrant des soins primaires, diagnostiquant diverses affections et orientant les patients vers des spécialistes si nécessaire. Tisse des liens solides avec les patients, jouant un rôle essentiel dans la promotion de la santé communautaire.",
                'salary' =>  700.00,
                'img_job' => 'images/jobs/sante/medecin_generaliste.png',
                'diplomas_id' => 2,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Chirurgien",
                'description_1' => "Traitant des blessures, des maladies et des malformations par une variété de techniques chirurgicales avancées. Sauvant des vies avec habileté, précision et décisions rapides sous pression. Symbole d'espoir et de rétablissement pour les patients.",

                'salary' => 1000.00,
                'img_job' => 'images/jobs/sante/chirurgien.png',
                'diplomas_id' => 3,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Psychiatre",
                'description_1' =>  "Spécialisé dans la prévention, le diagnostic et le traitement des troubles psychologiques. Utilisant une approche intégrative pour guider les patients vers le rétablissement émotionnel et le bien-être mental. Jouant un rôle essentiel dans la promotion de la santé mentale.",

                'salary' => 800.00,
                'img_job' => 'images/jobs/sante/psychiatre.png',
                'diplomas_id' => 4,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Infirmier",
                'description_1' => "Fournissant un soutien essentiel aux patients en tant que premier point de contact dans leur parcours de soins. Rôle polyvalent incluant l'administration de traitements, la surveillance des signes vitaux et la coordination des soins avec l'équipe médicale. Incarnant l'humanité et la compassion.",

                'salary' => 600.00,
                'img_job' => 'images/jobs/sante/infirmier.png',
                'diplomas_id' => 5,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Pharmacologue",
                'description_1' => "Contribuant de manière significative à la découverte et au développement de nouveaux médicaments. Expertise en chimie, biologie et pharmacologie pour évaluer l'efficacité et la sécurité des traitements médicaux. Crucial pour faire avancer la médecine et améliorer la qualité de vie des patients.",

                'salary' => 750.00,
                'img_job' => 'images/jobs/sante/pharmacologue.png',
                'diplomas_id' => 6,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Médecin Généraliste',
                'description_1' => "Première ligne de défense contre la maladie, offrant des soins primaires, diagnostiquant diverses affections et orientant les patients vers des spécialistes si nécessaire. Tisse des liens solides avec les patients, jouant un rôle essentiel dans la promotion de la santé communautaire.",
                'salary' =>  700.00,
                'img_job' => 'images/jobs/sante/medecin_generaliste.png',
                'diplomas_id' => 2,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Chirurgien",
                'description_1' => "Traitant des blessures, des maladies et des malformations par une variété de techniques chirurgicales avancées. Sauvant des vies avec habileté, précision et décisions rapides sous pression. Symbole d'espoir et de rétablissement pour les patients.",

                'salary' => 1000.00,
                'img_job' => 'images/jobs/sante/chirurgien.png',
                'diplomas_id' => 3,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Psychiatre",
                'description_1' =>  "Spécialisé dans la prévention, le diagnostic et le traitement des troubles psychologiques. Utilisant une approche intégrative pour guider les patients vers le rétablissement émotionnel et le bien-être mental. Jouant un rôle essentiel dans la promotion de la santé mentale.",

                'salary' => 800.00,
                'img_job' => 'images/jobs/sante/psychiatre.png',
                'diplomas_id' => 4,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Infirmier",
                'description_1' => "Fournissant un soutien essentiel aux patients en tant que premier point de contact dans leur parcours de soins. Rôle polyvalent incluant l'administration de traitements, la surveillance des signes vitaux et la coordination des soins avec l'équipe médicale. Incarnant l'humanité et la compassion.",

                'salary' => 600.00,
                'img_job' => 'images/jobs/sante/infirmier.png',
                'diplomas_id' => 5,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Pharmacologue",
                'description_1' => "Contribuant de manière significative à la découverte et au développement de nouveaux médicaments. Expertise en chimie, biologie et pharmacologie pour évaluer l'efficacité et la sécurité des traitements médicaux. Crucial pour faire avancer la médecine et améliorer la qualité de vie des patients.",

                'salary' => 750.00,
                'img_job' => 'images/jobs/sante/pharmacologue.png',
                'diplomas_id' => 6,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => "",
            //     'description_1' => "",
            //     'description_2' => "",
            //     'salary' => 5000.00,
            //     'img_job' => 'chemin/vers/limage1.jpg',
            //     'diplomas_id' => 2,
            //     'places_id' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ]);
    }
}
