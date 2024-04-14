<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Study;

class StudiesSeeder extends Seeder
{
    public function run()
    {
        Study::create([
            'name' =>
            'Année Générale Sciences de la Santé',
            'category' => 'Science',
            'description_1' => 'Plonge dans le monde fascinant de la santé, où chaque découverte et chaque soin compte. Ces premiers pas te préparent à comprendre le corps humain, ses mystères et les innombrables façons de le guérir et de le chérir.',
            'price' => 500.00,
            'duration' => 4,
            'img_study' => 'images/jobs/sante/1annee.png',
            'description_2' => "Félicitations pour l'obtention de ton diplôme en Année Générale en Sciences de la Santé ! Tu as désormais une base solide dans les principes fondamentaux de la santé humaine, te préparant à poursuivre des études spécialisées.",
            'diplomas_id' => 1,
            'diplomas_required_id' => null,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation Médecine Générale',
            'category' => 'Science',
            'description_1' => 'Deviens le premier rempart contre la maladie, le confident et le guide de tes patients. En médecine générale, chaque jour est un nouveau défi, une nouvelle chance de faire la différence dans la vie des gens.',
            'price' => 1200.00,
            'duration' => 12,
            'img_study' => 'images/jobs/sante/medecin_generaliste.png',
            'description_2' => "Bravo pour avoir obtenu ta spécialisation en Médecine Générale ! Tu es maintenant un médecin généraliste compétent, prêt à fournir des soins complets et holistiques à tes patients, tout en étant leur premier point de contact pour les problèmes de santé.",
            'diplomas_id' => 2,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation Chirurgie',
            'category' => 'Science',
            'description_1' => "Entre dans le monde de précision de la chirurgie, où chaque geste peut sauver une vie. Ta habileté et ton calme sous pression font de toi le héros dans l'ombre, redonnant espoir et santé à ceux qui en ont besoin.",
            'price' => 1500.00,
            'duration' => 15,
            'img_study' => 'images/jobs/sante/chirurgien.png',
            'description_2' => "Félicitations pour ta spécialisation en Chirurgie ! Tu as acquis les compétences techniques et la précision nécessaires pour pratiquer la chirurgie avec succès, sauvant des vies et améliorant la qualité de vie des patients grâce à ton expertise.",
            'diplomas_id' => 3,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation Psychiatrie',
            'category' => 'Science',
            'description_1' => "Explore les profondeurs de l'esprit humain et deviens un pilier de soutien pour ceux qui luttent contre des adversaires invisibles. En psychiatrie, tu apportes la lumière là où il y a des ombres, changeant des vies une conversation à la fois.",
            'price' => 1300.00,
            'duration' => 12,
            'img_study' => 'images/jobs/sante/psychiatre.png',
            'description_2' => "Bravo pour avoir terminé ta spécialisation en Psychiatrie ! Tu es maintenant un expert dans l'évaluation, le diagnostic et le traitement des troubles mentaux, offrant un soutien crucial à ceux qui en ont besoin et contribuant à l'amélioration de leur bien-être mental.",
            'diplomas_id' => 4,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation Soins Infirmiers',
            'category' => 'Science',
            'description_1' => "Sois le cœur battant du secteur de la santé, veillant sur tes patients jour et nuit. En soins infirmiers, ta compassion et ton dévouement font toute la différence, apportant confort et guérison à chaque étape.",
            'price' => 900.00,
            'duration' => 8,
            'img_study' => 'images/jobs/sante/infirmier.png',
            'description_2' => "Félicitations pour ton diplôme en Spécialisation en Soins Infirmiers ! En tant qu'infirmier qualifié, tu es le pilier du système de santé, fournissant des soins attentionnés et compétents à tes patients tout en apportant confort et soutien à leurs familles.",
            'diplomas_id' => 5,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation Pharmacologie',
            'category' => 'Science',
            'description_1' => "Plonge dans l'univers complexe des médicaments, où chaque molécule a le pouvoir de guérir ou de protéger. La pharmacologie est ta chance de combattre la maladie au niveau le plus fondamental, avec sagesse et innovation.",
            'price' => 1100.00,
            'duration' => 10,
            'img_study' => 'images/jobs/sante/pharmacologue.png',
            'description_2' => "Bravo pour avoir complété ta spécialisation en Pharmacologie ! Tu es maintenant un expert dans l'étude des médicaments et de leur impact sur le corps humain, contribuant à l'avancement de la science médicale et à l'amélioration des traitements disponibles pour les patients.",
            'diplomas_id' => 6,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
    }
}
