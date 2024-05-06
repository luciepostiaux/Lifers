<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobsSeeder extends Seeder
{
    public function run()
    {
        Job::insert([
            // Sans diplôme
            [
                'name' => 'Artisan',
                'description_1' => "En tant qu'Artisan, vous créez des objets uniques et de haute qualité avec passion, que ce soit la menuiserie, la poterie ou la joaillerie.",
                'salary' =>  500.00,
                'img_job' => 'images/jobs/no_diploma/artisan.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Commerçant",
                'description_1' => "Comme Commerçant, vous offrez des produits et des services essentiels à la communauté, que vous teniez une boutique, un kiosque ou une boutique en ligne.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/commerce.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Restaurateur",
                'description_1' =>  "En tant que Restaurateur, vous créez des expériences culinaires rassembleuses, que vous dirigiez un restaurant familial, une brasserie branchée ou un food truck innovant.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/restaurateur.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Agriculteur",
                'description_1' => "Comme Agriculteur, vous cultivez les aliments qui nourrissent notre société avec dévouement, que ce soit dans les champs, les vergers ou les serres.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/agriculteur.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Coach Sportif",
                'description_1' => "En tant que Coach Sportif, vous inspirez et motivez les gens à atteindre leurs objectifs de fitness et de bien-être, que ce soit en salle de sport, en ligne ou en plein air.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/sport.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Coiffeur",
                'description_1' => "Comme Coiffeur, vous transformez chaque mèche en une œuvre d'art, que ce soit dans un salon chic, un studio privé ou à domicile.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/coiffeur.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Styliste",
                'description_1' => "En tant que Styliste, vous créez des looks et des collections qui définissent les tendances, que vous travailliez pour des marques de haute couture, des défilés de mode ou des clients privés.",

                'salary' => 500.00,
                'img_job' => 'images/jobs/no_diploma/styliste.webp',
                'diplomas_id' => null,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Santé
            [
                'name' => 'Médecin Généraliste',
                'description_1' => "Première ligne de défense contre la maladie, offrant des soins primaires, diagnostiquant diverses affections et orientant les patients vers des spécialistes si nécessaire. Tisse des liens solides avec les patients, jouant un rôle essentiel dans la promotion de la santé communautaire.",
                'salary' =>  700.00,
                'img_job' => 'images/jobs/sante/medecin_generaliste.webp',
                'diplomas_id' => 2,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Chirurgien",
                'description_1' => "Traitant des blessures, des maladies et des malformations par une variété de techniques chirurgicales avancées. Sauvant des vies avec habileté, précision et décisions rapides sous pression. Symbole d'espoir et de rétablissement pour les patients.",

                'salary' => 1000.00,
                'img_job' => 'images/jobs/sante/chirurgien.webp',
                'diplomas_id' => 3,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Psychiatre",
                'description_1' =>  "Spécialisé dans la prévention, le diagnostic et le traitement des troubles psychologiques. Utilisant une approche intégrative pour guider les patients vers le rétablissement émotionnel et le bien-être mental. Jouant un rôle essentiel dans la promotion de la santé mentale.",

                'salary' => 800.00,
                'img_job' => 'images/jobs/sante/psychiatre.webp',
                'diplomas_id' => 4,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Infirmier",
                'description_1' => "Fournissant un soutien essentiel aux patients en tant que premier point de contact dans leur parcours de soins. Rôle polyvalent incluant l'administration de traitements, la surveillance des signes vitaux et la coordination des soins avec l'équipe médicale. Incarnant l'humanité et la compassion.",

                'salary' => 600.00,
                'img_job' => 'images/jobs/sante/infirmier.webp',
                'diplomas_id' => 5,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Pharmacologue",
                'description_1' => "Contribuant de manière significative à la découverte et au développement de nouveaux médicaments. Expertise en chimie, biologie et pharmacologie pour évaluer l'efficacité et la sécurité des traitements médicaux. Crucial pour faire avancer la médecine et améliorer la qualité de vie des patients.",

                'salary' => 750.00,
                'img_job' => 'images/jobs/sante/pharmacologue.webp',
                'diplomas_id' => 6,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Technologie
            [
                'name' => 'Ingénieur Civil',
                'description_1' => "En tant qu'Ingénieur Civil, assurez la conception, la construction et l'entretien d'infrastructures vitales telles que ponts, routes et bâtiments, garantissant la sécurité et l'efficacité pour le bien-être public.",
                'salary' =>  750.00,
                'img_job' => 'images/jobs/techno/civil.webp',
                'diplomas_id' => 8,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Ingénieur Mécanique",
                'description_1' => "En tant qu'Ingénieur Mécanique, concevez, développez et testez machines et systèmes, moteurs de l'innovation industrielle, optimisant la technologie et la production pour un impact majeur sur le secteur.",
                'salary' => 800.00,
                'img_job' => 'images/jobs/techno/mecanique.webp',
                'diplomas_id' => 9,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Ingénieur Électrique",
                'description_1' => "En tant qu'Ingénieur Électrique, concevez et améliorez systèmes électriques et électroniques, alimentant notre monde moderne, des circuits microscopiques aux réseaux de distribution d'énergie, récompensant expertise et créativité.",
                'salary' => 850.00,
                'img_job' => 'images/jobs/techno/electrique.webp',
                'diplomas_id' => 10,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Ingénieur Informatique",
                'description_1' => "En tant que Développeur ou Ingénieur Informatique, façonnez l'ère numérique en concevant et codant logiciels, applications et systèmes, moteurs du progrès et de l'innovation dans tous les secteurs.",
                'salary' => 900.00,
                'img_job' => 'images/jobs/techno/dev.webp',
                'diplomas_id' => 11,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Ingénieur Aérospatial",
                'description_1' => "En tant qu'Ingénieur Aérospatial, concevez et testez aéronefs, satellites et engins spatiaux, explorant le ciel et l'espace, repoussant les limites de la science et de l'ingénierie. Votre expertise stratégique et innovante est cruciale pour les avancées aérospatiales.",
                'salary' => 1000.00,
                'img_job' => 'images/jobs/techno/spatial.webp',
                'diplomas_id' => 12,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sciences Humaines et Sociales
            [
                'name' => 'Psychologue ',
                'description_1' => "En tant que Psychologue, explorez les méandres de l'esprit humain, offrant soutien et conseils pour le bien-être mental. Votre travail transforme des vies en offrant des perspectives et des stratégies pour surmonter les défis personnels, reconnaissant l'impact profond sur les individus.",
                'salary' =>  750.00,
                'img_job' => 'images/jobs/social/psychologue.webp',
                'diplomas_id' => 14,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Sociologue",
                'description_1' => "En tant que Sociologue, analysez les structures sociales, étudiant les interactions et transformations des groupes humains. Votre travail fournit des insights clés sur les dynamiques sociales, influençant politiques et pratiques pour améliorer le tissu social.",
                'salary' => 800.00,
                'img_job' => 'images/jobs/social/sociologue.webp',
                'diplomas_id' => 15,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Anthropologue",
                'description_1' =>  "En tant qu'Anthropologue, explorez la diversité culturelle humaine, étudiant traditions, rites et structures sociales à travers le temps et l'espace. Votre recherche éclaire notre compréhension collective de l'humanité, soulignant la valeur de votre contribution.",
                'salary' => 850.00,
                'img_job' => 'images/jobs/social/anthropologue.webp',
                'diplomas_id' => 16,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Avocat / Juriste",
                'description_1' => "En tant qu'Avocat ou Juriste, défendez la justice en représentant individus, entreprises et institutions dans le système juridique complexe. Votre expertise est cruciale pour naviguer les lois et réglementations, maintenant l'ordre et l'équité dans la société.",
                'salary' => 900.00,
                'img_job' => 'images/jobs/social/juriste.webp',
                'diplomas_id' => 17,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Économiste ",
                'description_1' => "En tant qu'Économiste, analysez les forces qui guident nos marchés et notre économie, influençant politiques et décisions d'affaires pour la stabilité et la croissance économique. Votre expertise stratégique est cruciale dans la gestion des ressources.",
                'salary' => 1000.00,
                'img_job' => 'images/jobs/social/economiste.webp',
                'diplomas_id' => 18,
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
