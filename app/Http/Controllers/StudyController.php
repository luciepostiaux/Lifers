<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyStudyRequest;
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
        $perso = $user->perso()->with(['enrolledStudies.study', 'diplomas'])->first();
        $latestEnrollment = $perso->enrolledStudies->sortByDesc('created_at')->first();
        $currentStudyDetails = null; // Initialise la variable
        if ($latestEnrollment && $latestEnrollment->study) {
            $currentStudyDetails = [
                'id' => $latestEnrollment->study->id,
                'name' => $latestEnrollment->study->name,
                'description' => $latestEnrollment->study->description_1,
                'img' => $latestEnrollment->study->img_study,
                'start_date' => $latestEnrollment->start_date,
                'end_date' => $latestEnrollment->end_date,
            ];
        }
        return Inertia::render('Study/Index', [
            'studies' => $studies,
            'currentStudy' => $currentStudyDetails,
            'persoDiplomas' => $perso->diplomas,

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
        if (!$personnage) {
            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $study = Study::findOrFail($studyId);

        // Vérifiez si le personnage a suffisamment d'argent pour s'inscrire à l'étude
        if ($personnage->money < $study->price) {
            return redirect()->back()->withErrors(['msg' => 'Vous n’avez pas suffisamment d’argent pour vous inscrire à cette étude.']);
        }

        if ($study->required_diploma_id && !$personnage->diplomas->contains('id', $study->required_diploma_id)) {
            return redirect()->back()->withErrors(['msg' => 'Vous devez avoir le diplôme requis pour vous inscrire à cette étude.']);
        }

        DB::beginTransaction();
        try {
            // Supprimez toutes les inscriptions existantes avant d'en créer une nouvelle
            $personnage->enrolledStudies()->delete();

            // Déduisez le coût de l'étude de l'argent du personnage
            $personnage->money -= $study->price;
            $personnage->save();

            // Création d'une nouvelle inscription
            $endDate = now()->addDays($study->duration)->format('Y-m-d');
            $personnage->enrolledStudies()->create([
                'study_id' => $studyId,
                'end_date' => $endDate,
            ]);

            // Validez la transaction
            DB::commit();
            return redirect()->route('study.index')->with('message', 'Inscription à l’étude réussie et paiement effectué.');
        } catch (\Exception $e) {
            // Si une erreur survient, annulez la transaction
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

    public function claimDiploma(Request $request, $studyId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {
            return redirect()->route('study.index')->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $study = Study::findOrFail($studyId);
        $diploma = $study->diploma;
        if (!$diploma) {
            return redirect()->route('study.index')->withErrors(['msg' => 'Cette étude ne propose aucun diplôme.']);
        }

        if (!$personnage->diplomas->contains('id', $diploma->id)) {
            $personnage->diplomas()->attach($diploma->id);

            $enrollment = PersoStudyEnrollment::where('perso_id', $personnage->id)
                ->where('study_id', $study->id)
                ->first();
            if ($enrollment) {
                $enrollment->delete();
            }

            return redirect()->route('study.index')->with('message', 'Diplôme récupéré avec succès. Inscription aux études terminée.');
        } else {
            return redirect()->route('study.index')->withErrors(['msg' => 'Le personnage possède déjà ce diplôme.']);
        }
    }
}
