<?php

namespace App\Http\Controllers;

use App\Models\PersoStudyEnrollment;
use App\Models\Study;
use Illuminate\Http\Request;
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

    public function enroll(Request $request, $studyId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {
            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $study = Study::findOrFail($studyId);

        // Assurez-vous que le personnage remplit les conditions pour s'inscrire (par exemple, a le diplôme requis).
        if ($study->requiredDiploma && !$personnage->diplomas->contains('id', $study->requiredDiploma->id)) {
            return redirect()->back()->withErrors(['msg' => 'Vous devez avoir le diplôme requis pour vous inscrire à cette étude.']);
        }

        // Enregistrer la nouvelle inscription.
        DB::beginTransaction();
        try {
            // Vous pouvez vouloir ajouter une logique pour vérifier s'il est déjà inscrit.
            $enrollment = new PersoStudyEnrollment([
                'perso_id' => $personnage->id,
                'study_id' => $studyId,
                // Définir d'autres propriétés d'inscription si nécessaire
            ]);
            $enrollment->save();

            DB::commit();
            return redirect()->route('study.current', ['id' => $studyId])->with('message', 'Inscription à l’étude réussie.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors de l’inscription à l’étude.']);
        }
    }


    public function dropCurrentStudy()
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {
            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        // Récupérer l'inscription à l'étude actuelle
        $latestEnrollment = $personnage->enrolledStudies()->latest()->first(); // Utiliser la relation définie pour obtenir la dernière inscription

        if (!$latestEnrollment) {
            return redirect()->back()->withErrors(['msg' => 'Aucune étude en cours associée à ce personnage.']);
        }

        // Commencer la transaction
        DB::beginTransaction();
        try {
            // Supprimer l'inscription à l'étude actuelle
            $latestEnrollment->delete(); // Cela supprime l'inscription mais ne change pas l'étude elle-même

            // Valider la transaction
            DB::commit();
            return redirect()->route('study')->with('message', 'Vous avez abandonné vos études avec succès.');
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors de l’abandon des études.']);
        }
    }
}
