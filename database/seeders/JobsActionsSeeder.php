<?php

namespace Database\Seeders;

use App\Models\JobsAction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobsActionsSeeder extends Seeder
{
    public function run()
    {
        // Actions pour le job_id 1
        $actionsMedecin = [
            [
                'name' => 'Consultation',
                'description' => 'Consultations médicales générales pour des bilans de santé.',
                'price' => 50.00,
                'chance' => 50,
                'jobs_id' => 1,
            ],
            [
                'name' => 'Diagnostic',
                'description' => 'Diagnostic et traitement de maladies courantes.',
                'price' => 75.00,
                'chance' => 25,
                'jobs_id' => 1,
            ], [
                'name' => 'Prescription',
                'description' => 'Prescription et gestion de traitements médicamenteux.',
                'price' => 80.00,
                'chance' => 15,
                'jobs_id' => 1,
            ], [
                'name' => 'Réorientation',
                'description' => 'Orientation des patients vers des spécialistes pour des soins plus spécifiques.',
                'price' => 100.00,
                'chance' => 6,
                'jobs_id' => 1,
            ], [
                'name' => 'Suivi',
                'description' => 'Suivi et prise en charge des patients chroniques.',
                'price' => 250.00,
                'chance' => 2,
                'jobs_id' => 1,
            ],
        ];
        $actionsChirurgien = [
            [
                'name' => 'Chirurgies mineures',
                'description' => 'Réalisation de chirurgies mineures programmées.',
                'price' => 50.00,
                'chance' => 50,
                'jobs_id' => 2,
            ],
            [
                'name' => 'Consultations préopératoires',
                'description' => 'Consultations préopératoires et évaluations de risque chirurgical.',
                'price' => 75.00,
                'chance' => 25,
                'jobs_id' => 2,
            ], [
                'name' => 'Suivi postopératoire',
                'description' => 'Suivi postopératoire et gestion de la récupération des patients.',
                'price' => 80.00,
                'chance' => 15,
                'jobs_id' => 2,
            ], [
                'name' => 'Urgences chirurgicales',
                'description' => 'Urgences chirurgicales non planifiées.',
                'price' => 100.00,
                'chance' => 6,
                'jobs_id' => 2,
            ], [
                'name' => 'Multidisciplinaires',
                'description' => 'Participation à des équipes multidisciplinaires pour des cas complexes nécessitant une intervention chirurgicale.',
                'price' => 250.00,
                'chance' => 2,
                'jobs_id' => 2,
            ],
        ];
        $actionsPsychiatre = [
            [
                'name' => 'Séances de thérapie',
                'description' => 'Séances de thérapie et de conseil pour des troubles mentaux courants.',
                'price' => 50.00,
                'chance' => 50,
                'jobs_id' => 3,
            ],
            [
                'name' => 'Prescription',
                'description' => 'Prescription et ajustement de traitements psychiatriques médicamenteux.',
                'price' => 75.00,
                'chance' => 25,
                'jobs_id' => 3,
            ], [
                'name' => 'Évaluations diagnostiques',
                'description' => 'Évaluations diagnostiques pour de nouveaux patients.',
                'price' => 80.00,
                'chance' => 15,
                'jobs_id' => 3,
            ], [
                'name' => 'Gestion et suivi',
                'description' => 'Gestion et suivi de troubles mentaux chroniques ou graves.',
                'price' => 100.00,
                'chance' => 6,
                'jobs_id' => 3,
            ], [
                'name' => 'Intervention',
                'description' => 'Intervention en cas de crises psychiatriques aiguës.',
                'price' => 250.00,
                'chance' => 2,
                'jobs_id' => 3,
            ],
        ];
        $actionsInfirmier = [
            [
                'name' => 'Administration',
                'description' => 'Administration quotidienne de médicaments et soins de base.',
                'price' => 50.00,
                'chance' => 50,
                'jobs_id' => 4,
            ],
            [
                'name' => 'Suivi des signes vitaux',
                'description' => "Suivi des signes vitaux et surveillance de l'état de santé des patients.",
                'price' => 75.00,
                'chance' => 25,
                'jobs_id' => 4,
            ], [
                'name' => 'Assistance aux médecins',
                'description' => 'Assistance aux médecins durant les examens et les procédures médicales.',
                'price' => 80.00,
                'chance' => 15,
                'jobs_id' => 4,
            ], [
                'name' => 'Éducation des patients',
                'description' => 'Éducation des patients sur la gestion de leur santé et prévention.',
                'price' => 100.00,
                'chance' => 6,
                'jobs_id' => 4,
            ], [
                'name' => "Interventions d'urgence",
                'description' => "Interventions d'urgence pour des soins immédiats en cas d'incident.",
                'price' => 250.00,
                'chance' => 2,
                'jobs_id' => 4,
            ],
        ];
        $actionsPharmacologue = [
            [
                'name' => 'Recherche',
                'description' => 'Recherche et développement de nouveaux médicaments.',
                'price' => 50.00,
                'chance' => 50,
                'jobs_id' => 5,
            ],
            [
                'name' => "Réalisation d'essais",
                'description' => "Réalisation d'essais cliniques pour évaluer l'efficacité et la sécurité des médicaments.",
                'price' => 75.00,
                'chance' => 25,
                'jobs_id' => 5,
            ], [
                'name' => 'Analyse',
                'description' => 'Analyse des interactions médicamenteuses et des effets secondaires.',
                'price' => 80.00,
                'chance' => 15,
                'jobs_id' => 5,
            ], [
                'name' => 'Consultation',
                'description' => 'Consultation sur la prescription de médicaments complexes.',
                'price' => 100.00,
                'chance' => 6,
                'jobs_id' => 5,
            ], [
                'name' => "Contribution",
                'description' => "Contribution à la rédaction de publications scientifiques et rapports de recherche.",
                'price' => 250.00,
                'chance' => 2,
                'jobs_id' => 5,
            ],
        ];

        JobsAction::insert($actionsMedecin);
        JobsAction::insert($actionsChirurgien);
        JobsAction::insert($actionsPsychiatre);
        JobsAction::insert($actionsInfirmier);
        JobsAction::insert($actionsPharmacologue);
    }
}
