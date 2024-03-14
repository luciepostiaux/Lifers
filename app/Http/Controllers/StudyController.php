<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyStudyRequest;
use App\Models\PersoStudyEnrollment;
use App\Models\Study;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $study = Study::findOrFail($id);
        $user = Auth::user();
        $perso = $user->perso;

        // Recherchez l'inscription du personnage à cette étude spécifique
        $enrollment = null;
        if ($perso) {
            $enrollment = PersoStudyEnrollment::where('perso_id', $perso->id)
                ->where('study_id', $study->id)
                ->latest()
                ->first();
        }
        return Inertia::render('Study/Current', [
            'studyDetails' => $study,
            'enrollmentDetails' => $enrollment,
        ]);
    }

    public function enroll(ApplyStudyRequest $request, $studyId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        // Vérifiez si le personnage est défini et a les droits nécessaires
        if (!$personnage) {
            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $study = Study::findOrFail($studyId); // Trouvez l'étude

        // Si le personnage n'a pas le diplôme requis (si un diplôme est requis pour l'étude)
        if ($study->required_diploma_id && !$personnage->diplomas->contains('id', $study->required_diploma_id)) {
            return redirect()->back()->withErrors(['msg' => 'Vous devez avoir le diplôme requis pour vous inscrire à cette étude.']);
        }

        // Vérifier si le personnage est déjà inscrit à cette étude et que les études ne sont pas encore terminées
        $existingEnrollment = $personnage->enrolledStudies()->where('study_id', $studyId)->where('end_date', '>', now())->first();
        if ($existingEnrollment) {
            return redirect()->back()->withErrors(['msg' => 'Le personnage est déjà inscrit à cette étude et elle n\'est pas encore terminée.']);
        }

        // Créer une nouvelle inscription dans la base de données avec la durée de l'étude
        DB::beginTransaction();
        try {
            // Calculer la date de fin en fonction de la durée de l'étude
            $endDate = now()->addDays($study->duration);

            $enrollment = new PersoStudyEnrollment([
                'perso_id' => $personnage->id,
                'study_id' => $studyId,
                'start_date' => now(),
                'end_date' => $endDate,  // Ajoutez la date de fin basée sur la durée de l'étude
            ]);
            $enrollment->save();

            DB::commit();
            return redirect()->route('study.index')->with('message', 'Inscription à l’étude réussie.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors de l’inscription à l’étude.']);
        }
    }

    public function resign()
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {
            return redirect()->route('study.index')->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        // Utilisez la méthode que nous venons d'ajouter pour résigner de l'étude
        $personnage->resignFromStudy();

        return redirect()->route('study.index')->with('message', 'Vous avez quitté l\'étude avec succès.');
    }



    // public function dropCurrentStudy()
    // {
    //     $user = Auth::user();
    //     $personnage = $user->perso;

    //     if (!$personnage) {
    //         return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
    //     }

    //     // Récupérer l'inscription à l'étude actuelle
    //     $latestEnrollment = $personnage->enrolledStudies()->latest()->first(); // Utiliser la relation définie pour obtenir la dernière inscription

    //     if (!$latestEnrollment) {
    //         return redirect()->back()->withErrors(['msg' => 'Aucune étude en cours associée à ce personnage.']);
    //     }

    //     // Commencer la transaction
    //     DB::beginTransaction();
    //     try {
    //         // Supprimer l'inscription à l'étude actuelle
    //         $latestEnrollment->delete(); // Cela supprime l'inscription mais ne change pas l'étude elle-même

    //         // Valider la transaction
    //         DB::commit();
    //         return redirect()->route('study')->with('message', 'Vous avez abandonné vos études avec succès.');
    //     } catch (\Exception $e) {
    //         // Annuler la transaction en cas d'erreur
    //         DB::rollBack();
    //         return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors de l’abandon des études.']);
    //     }
    // }
}
