<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Study;

class StudiesSeeder extends Seeder
{
    public function run()
    {
        // Sciences de la Santé

        Study::create([
            'name' =>
            'Année Générale Sciences de la Santé',
            'category' => 'Science',
            'description_1' => 'Plonge dans le monde fascinant de la santé, où chaque découverte et chaque soin compte. Ces premiers pas te préparent à comprendre le corps humain, ses mystères et les innombrables façons de le guérir et de le chérir.',
            'price' => 500.00,
            'duration' => 1,
            'img_study' => 'images/jobs/first.webp',
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
            'duration' => 5,
            'img_study' => 'images/jobs/sante/medecin_generaliste.webp',
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
            'duration' => 6,
            'img_study' => 'images/jobs/sante/chirurgien.webp',
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
            'duration' => 6,
            'img_study' => 'images/jobs/sante/psychiatre.webp',
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
            'duration' => 4,
            'img_study' => 'images/jobs/sante/infirmier.webp',
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
            'duration' => 6,
            'img_study' => 'images/jobs/sante/pharmacologue.webp',
            'description_2' => "Bravo pour avoir complété ta spécialisation en Pharmacologie ! Tu es maintenant un expert dans l'étude des médicaments et de leur impact sur le corps humain, contribuant à l'avancement de la science médicale et à l'amélioration des traitements disponibles pour les patients.",
            'diplomas_id' => 6,
            'diplomas_required_id' => 1,
            'places_id' => 1,
        ]);
        // Ingénierie et Technologie
        Study::create([
            'name' =>
            'Année Générale Ingénierie et Technologie',
            'category' => 'Technologie',
            'description_1' => 'Lance-toi dans une aventure où chaque problème est une énigme à résoudre. Cette première année te donne les bases pour comprendre comment, avec de la créativité et de la logique, tu peux construire le monde de demain.',
            'price' => 500.00,
            'duration' => 1,
            'img_study' => 'images/jobs/first.webp',
            'description_2' => "Félicitations pour l'obtention de ton diplôme en Année Générale en Ingénierie et Technologie ! Tu as maintenant les compétences de base nécessaires pour aborder les défis complexes du monde de l'ingénierie et de la technologie, prêt à les résoudre avec créativité et logique.",
            'diplomas_id' => 7,
            'diplomas_required_id' => null,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Génie Civil',
            'category' => 'Technologie',
            'description_1' => "Construis les fondations d'un avenir meilleur en devenant un expert en génie civil. Des gratte-ciel majestueux aux ponts qui relient les communautés, chaque projet est un témoignage de ton ingéniosité et de ta vision.",
            'price' => 1800.00,
            'duration' => 6,
            'img_study' => 'images/jobs/techno/civil.webp',
            'description_2' => "Bravo pour ta spécialisation en Génie Civil ! En tant qu'expert, tu es désormais capable de concevoir et de superviser la construction de structures essentielles telles que des bâtiments, des routes et des ponts, contribuant ainsi à façonner le monde physique qui nous entoure.",
            'diplomas_id' => 8,
            'diplomas_required_id' => 7,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Génie Mécanique',
            'category' => 'Technologie',
            'description_1' => "Plonge dans le monde fascinant où la mécanique et l'innovation se rencontrent. En génie mécanique, tu conçois et améliores machines et systèmes, rendant la vie quotidienne plus facile et plus efficace.",
            'price' => 1500.00,
            'duration' => 15,
            'img_study' => 'images/jobs/techno/mecanique.webp',
            'description_2' => "Félicitations pour avoir obtenu ta spécialisation en Génie Mécanique ! Tu as maintenant les compétences nécessaires pour concevoir, analyser et améliorer une variété de systèmes mécaniques, contribuant ainsi à rendre notre vie quotidienne plus efficace et plus confortable.",
            'diplomas_id' => 9,
            'diplomas_required_id' => 7,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Génie Électrique',
            'category' => 'Technologie',
            'description_1' => "Éclaire le monde en maîtrisant le pouvoir de l'électricité. En génie électrique, tu es au cœur de l'innovation, développant des technologies qui alimentent nos maisons, nos villes et bien au-delà.",
            'price' => 1300.00,
            'duration' => 12,
            'img_study' => 'images/jobs/techno/electrique.webp',
            'description_2' => "Bravo pour ta spécialisation en Génie Électrique ! En tant qu'ingénieur électricien, tu es maintenant capable de concevoir, développer et superviser des systèmes électriques complexes, participant ainsi à l'avancement des technologies qui alimentent notre monde moderne.",
            'diplomas_id' => 10,
            'diplomas_required_id' => 7,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Informatique',
            'category' => 'Technologie',
            'description_1' => "Entre dans l'ère numérique en devenant un expert en informatique. Tu as le pouvoir de coder, de créer et de connecter, transformant des lignes de code en solutions qui redéfinissent notre façon de vivre, de travailler et de jouer.",
            'price' => 900.00,
            'duration' => 8,
            'img_study' => 'images/jobs/techno/dev.webp',
            'description_2' => "Félicitations pour ta spécialisation en Informatique ! En tant qu'expert en informatique, tu as le pouvoir de créer des solutions informatiques innovantes qui transforment notre façon de vivre, de travailler et de communiquer dans l'ère numérique.",
            'diplomas_id' => 11,
            'diplomas_required_id' => 7,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Aérospatiale',
            'category' => 'Technologie',
            'description_1' => "Vise les étoiles et au-delà en te spécialisant en aérospatiale. Conçois des avions, des satellites, et même des vaisseaux spatiaux, repoussant les frontières de ce qui est possible et explorant les derniers confins de notre univers.",
            'price' => 2000.00,
            'duration' => 12,
            'img_study' => 'images/jobs/techno/spatial.webp',
            'description_2' => "Bravo pour avoir terminé ta spécialisation en Aérospatiale ! Tu es maintenant un expert dans la conception et la fabrication de véhicules aériens et spatiaux, contribuant ainsi à l'exploration et à la compréhension des frontières de notre univers.",
            'diplomas_id' => 12,
            'diplomas_required_id' => 7,
            'places_id' => 1,
        ]);
        // Sciences Humaines et Sociales
        Study::create([
            'name' =>
            'Année Générale en Sciences Humaines et Sociales',
            'category' => 'Sociale',
            'description_1' => "Immerge-toi dans l'étude de la société et de l'humain, explorant comment nos pensées, nos cultures et nos systèmes façonnent notre monde. Cette année initiale te prépare à comprendre et à influencer positivement le tissu social complexe qui nous entoure.",
            'price' => 500.00,
            'duration' => 1,
            'img_study' => 'images/jobs/first.webp',
            'description_2' => "Félicitations pour l'obtention de ton diplôme en Année Générale en Sciences Humaines et Sociales ! Tu as désormais une compréhension approfondie de la société humaine et de ses dynamiques, te préparant à influencer positivement le tissu social qui nous entoure.",
            'diplomas_id' => 13,
            'diplomas_required_id' => null,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Psychologie',
            'category' => 'Sociale',
            'description_1' => "Plonge dans les profondeurs de l'esprit humain, décryptant les mystères de nos pensées, émotions et comportements. En psychologie, tu deviens le guide vers le bien-être et la compréhension de soi, changeant des vies une âme à la fois.",
            'price' => 1800.00,
            'duration' => 6,
            'img_study' => 'images/jobs/social/psychologue.webp',
            'description_2' => "Bravo pour ta spécialisation en Psychologie ! En tant que psychologue, tu es désormais un expert dans l'étude du comportement humain, aidant les individus à surmonter les défis et à réaliser leur plein potentiel.",
            'diplomas_id' => 14,
            'diplomas_required_id' => 13,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Sociologie',
            'category' => 'Sociale',
            'description_1' => "Observe, analyse et comprends les dynamiques de nos sociétés. La sociologie te donne les clés pour déchiffrer les structures sociales, les tendances et les transformations, te permettant d'apporter des solutions éclairées aux défis de nos communautés.",
            'price' => 1500.00,
            'duration' => 15,
            'img_study' => 'images/jobs/social/sociologue.webp',
            'description_2' => "Félicitations pour ta spécialisation en Sociologie ! En tant que sociologue, tu es maintenant capable d'analyser et de comprendre les structures sociales et les interactions humaines, contribuant ainsi à des changements positifs dans la société.",
            'diplomas_id' => 15,
            'diplomas_required_id' => 13,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Anthropologie',
            'category' => 'Sociale',
            'description_1' => "Embarque pour un voyage à travers les cultures et les époques, dévoilant la riche tapestry de l'humanité. En anthropologie, chaque découverte est un pas de plus vers la compréhension de ce qui nous unit et nous distingue, enrichissant notre vision du monde.",
            'price' => 1300.00,
            'duration' => 12,
            'img_study' => 'images/jobs/social/anthropologue.webp',
            'description_2' => "Bravo pour avoir obtenu ta spécialisation en Anthropologie ! En tant qu'anthropologue, tu es désormais un expert dans l'étude des cultures et des sociétés humaines, contribuant ainsi à une meilleure compréhension de la diversité et de la richesse de l'humanité.",
            'diplomas_id' => 16,
            'diplomas_required_id' => 13,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Droit',
            'category' => 'Sociale',
            'description_1' => "Deviens le gardien de la justice, maniant le droit comme un outil au service de l'équité et de l'ordre. La spécialisation en droit te positionne au cœur des systèmes qui régissent nos vies, te donnant le pouvoir de défendre, de conseiller et de guider.",
            'price' => 900.00,
            'duration' => 8,
            'img_study' => 'images/jobs/social/juriste.webp',
            'description_2' => "Félicitations pour ta spécialisation en Droit ! En tant qu'avocat, tu es désormais un défenseur de la justice et de l'équité, utilisant tes connaissances juridiques pour défendre les droits et les intérêts de tes clients.",
            'diplomas_id' => 17,
            'diplomas_required_id' => 13,
            'places_id' => 1,
        ]);
        Study::create([
            'name' => 'Spécialisation en Économie',
            'category' => 'Sociale',
            'description_1' => "Plonge au cœur des forces qui façonnent nos marchés et nos politiques. En économie, tu analyses, prévois et influences les tendances, jouant un rôle clé dans la création d'une prospérité durable pour tous.",
            'price' => 2000.00,
            'duration' => 12,
            'img_study' => 'images/jobs/social/economiste.webp',
            'description_2' => "Bravo pour ta spécialisation en Économie ! En tant qu'économiste, tu es désormais un expert dans l'analyse des forces économiques et des politiques publiques, contribuant ainsi à la prospérité et au bien-être de la société.",
            'diplomas_id' => 18,
            'diplomas_required_id' => 13,
            'places_id' => 1,
        ]);
    }
}
