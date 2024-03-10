<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudyController extends Controller
{
    public function index()
    {
        $studies = Study::all(); // Récupère toutes les études disponibles
        $user = Auth::user();
        $perso = $user->perso()->with(['enrolledStudies.study'])->first();

        $latestEnrollment = $perso->enrolledStudies->sortByDesc('created_at')->first();
        $currentStudyDetails = null; // Initialise la variable
        if ($latestEnrollment && $latestEnrollment->study) {
            $currentStudyDetails = [
                'id' => $latestEnrollment->study->id,
                'name' => $latestEnrollment->study->name,
                'description' => $latestEnrollment->study->description_1,
                'start_date' => $latestEnrollment->start_date,
                'end_date' => $latestEnrollment->end_date,
            ];
        }

        return Inertia::render('Study/Index', [
            'studies' => $studies,
            'currentStudy' => $currentStudyDetails,
        ]);
    }
    public function showCurrentStudy($id)
    {
        $study = Study::findOrFail($id); // ou une autre logique pour obtenir l'étude
        return Inertia::render('Study/Current', [
            'studyDetails' => $study,
        ]);
    }

    // Assurez-vous d'avoir la méthode currentStudy si nécessaire pour d'autres routes ou logiques
    public function currentStudy()
    {
        // Logique spécifique pour la page d'une étude en cours, si nécessaire
    }
}
