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
                'description_1' => "En tant que Médecin Généraliste, vous êtes la première ligne de défense contre la maladie, offrant des soins de santé primaires, diagnostiquant diverses affections et orientant les patients vers des spécialistes si nécessaire. Vous tissez des liens solides avec vos patients, les accompagnant tout au long de leur vie, et vous jouez un rôle essentiel dans la promotion de la santé communautaire. Votre pratique va bien au-delà de la médecine ; elle est un tissu complexe de relations humaines, de compréhension et de compassion.",
                'description_2' => "En tant que Médecin Généraliste, tu es plus qu'un soignant ; tu es un confident, un conseiller et un pilier sur lequel tes patients s'appuient tout au long de leur vie. Chaque jour, tu fais face à une mosaïque de défis, diagnostiquant et traitant une diversité d'affections, tout en offrant une oreille attentive et un soutien indéfectible. Ton rôle est essentiel non seulement dans la guérison des maux mais aussi dans la prévention des maladies et la promotion du bien-être. Tu es bien plus qu'un médecin ; tu es un gardien de la santé, un guide dans les moments d'incertitude et une source de réconfort dans les moments difficiles.",
                'salary' =>  7000.00,
                'img_job' => 'images/jobs/sante/medecin_generaliste.png',
                'diplomas_id' => 2,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Chirurgien",
                'description_1' => "Le rôle du Chirurgien est l'un des plus exigeants et gratifiants dans le domaine médical. Vous opérez pour traiter des blessures, des maladies et des malformations, en utilisant une variété de techniques chirurgicales avancées. Votre habileté, votre précision et votre capacité à prendre des décisions rapides sous pression peuvent littéralement sauver des vies. Votre travail va bien au-delà de la simple intervention ; vous êtes un symbole d'espoir et de rétablissement pour vos patients, une figure dont la détermination et le dévouement inspirent confiance et gratitude.",
                'description_2' => "Le chirurgien incarne une figure cruciale dans l'univers médical, un professionnel dont les compétences transcendent la médecine pour frôler l'art. Armé de techniques avancées et d'une précision inégalée, il fait face à des défis où chaque seconde compte, et où ses décisions rapides sous pression peuvent changer le cours d'une vie. Travaillant au carrefour de la science et de l'humanité, le chirurgien est plus qu'un médecin ; il est un sauveur, un guide dans les moments les plus critiques, utilisant son scalpel non seulement pour soigner mais pour restaurer l'espoir. Sa maîtrise des interventions chirurgicales, alliée à une capacité à naviguer dans l'urgence, fait de lui une pierre angulaire du monde médical, essentielle au bien-être et à la survie de ses patients.",
                'salary' => 10000.00,
                'img_job' => 'images/jobs/sante/chirurgien.png',
                'diplomas_id' => 3,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Psychiatre",
                'description_1' => "Le psychiatre est un professionnel de la santé mentale spécialisé dans la prévention, le diagnostic et le traitement des troubles psychologiques. Utilisant une approche intégrative combinant thérapies médicamenteuses et psychothérapeutiques, il guide ses patients vers le rétablissement émotionnel et le bien-être mental. Son rôle est essentiel dans la promotion de la santé mentale au sein de la société.",
                'description_2' => "En tant que Psychiatre, vous incarnez un pilier essentiel du système de santé mentale, œuvrant pour apaiser les tourments de l'esprit et restaurer l'équilibre émotionnel de vos patients. Vous vous spécialisez dans la prévention, le diagnostic et le traitement des troubles mentaux, naviguant avec compassion et expertise à travers les dédales complexes de la psyché humaine. Votre pratique s'étend bien au-delà des consultations traditionnelles, englobant souvent une combinaison de thérapies médicamenteuses et psychothérapeutiques pour répondre aux besoins uniques de chaque individu. En tant que guide et confident, vous offrez un espace sûr pour explorer les profondeurs de l'âme, aidant vos patients à surmonter les défis et à retrouver leur bien-être émotionnel. Votre travail est non seulement gratifiant sur le plan personnel, mais il revêt également une importance cruciale dans la promotion de la santé mentale et du bien-être dans la société.",
                'salary' => 8000.00,
                'img_job' => 'images/jobs/sante/psychiatre.png',
                'diplomas_id' => 4,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Infirmier",
                'description_1' => "L'Infirmier ou l'Infirmière est au cœur des soins de santé, fournissant un soutien essentiel aux patients et agissant comme le premier point de contact dans leur parcours de soins. Leur rôle polyvalent comprend l'administration de traitements, la surveillance des signes vitaux et la coordination des soins avec l'équipe médicale. Ils incarnent l'humanité et la compassion, offrant un soutien attentif à ceux dans le besoin.",
                'description_2' => "En tant qu'Infirmier ou Infirmière, vous incarnez l'essence même des soins de santé, travaillant avec dévouement et compassion pour assurer le bien-être physique et émotionnel des patients. Vous êtes le pilier sur lequel repose le système de santé, offrant un soutien continu et attentif aux personnes dans le besoin. Votre quotidien est marqué par une variété de tâches, allant de l'administration de traitements et de médicaments à la surveillance étroite des signes vitaux et à la coordination des soins avec l'équipe médicale. En tant que premier point de contact pour les patients, vous jouez un rôle crucial dans leur parcours de soins, offrant réconfort, soutien et expertise à chaque étape du chemin. Votre travail va bien au-delà des simples gestes techniques ; il est empreint d'humanité, de compassion et de dévouement envers ceux que vous servez.",
                'salary' => 5000.00,
                'img_job' => 'images/jobs/sante/infirmier.png',
                'diplomas_id' => 5,
                'places_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Pharmacologue",
                'description_1' => "Le Pharmacologue occupe une position stratégique dans le domaine de la santé, contribuant de manière significative à la découverte et au développement de nouveaux médicaments. Avec une expertise pointue en chimie, biologie et pharmacologie, ce professionnel mène des recherches essentielles pour évaluer l'efficacité et la sécurité des traitements médicaux. Son travail est crucial pour faire avancer la médecine et améliorer la qualité de vie des patients.",
                'description_2' => "En tant que Pharmacologue, vous occupez une position cruciale à l'intersection de la science et de la médecine, contribuant de manière significative à la découverte et au développement de nouveaux médicaments. Votre expertise repose sur une connaissance approfondie de la chimie, de la biologie et de la pharmacologie, vous permettant de comprendre en détail comment les substances chimiques interagissent avec les systèmes biologiques. Vous menez des recherches complexes pour évaluer l'efficacité et la sécurité des médicaments, collaborant souvent avec des équipes multidisciplinaires pour faire avancer les traitements médicaux. Votre travail est essentiel pour améliorer la qualité de vie des patients et pour relever les défis de santé publique, façonnant ainsi l'avenir des soins de santé.",
                'salary' => 7500.00,
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
