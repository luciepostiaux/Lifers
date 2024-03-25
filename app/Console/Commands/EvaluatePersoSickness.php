<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\Perso;
// use App\Models\Sickness;
// use Carbon\Carbon;

// class EvaluatePersoSickness extends Command
// {
//     protected $signature = 'evaluate:perso-sickness';
//     protected $description = 'Évalue et attribue aléatoirement des maladies aux personnages en fonction de leur âge et d’autres critères.';

//     public function handle()
//     {
//         $this->info('Évaluation des maladies des personnages en cours...');

//         // Récupérez tous les personnages
//         $personnages = Perso::all();

//         foreach ($personnages as $personnage) {
//             // Calculez l'âge du personnage
//             $age = $personnage->calculateAge();

//             // Obtenez toutes les maladies et évaluez pour chaque maladie si le personnage doit la développer
//             $maladies = Sickness::all();
//             foreach ($maladies as $maladie) {
//                 $this->evaluateSicknessForPerso($personnage, $maladie, $age);
//             }
//         }

//         $this->info('Évaluation terminée.');
//     }

//     protected function evaluateSicknessForPerso($personnage, $maladie, $age)
//     {
//         // Ici, vous ajouterez la logique pour évaluer si le personnage développe la maladie
//         // Cette méthode sera développée en fonction de vos critères de maladies et d'âge
//     }
// }