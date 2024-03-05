<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudyController extends Controller
{
    public function index()
    {
        $studies = Study::all();
        return Inertia::render('Study/Index', [
            'studies' => $studies,
        ]);
    }

    public function currentStudy()
    {
        $user = Auth::user();
        $perso = $user->perso()->with(['enrolledStudies.study'])->first();

        $latestEnrollment = $perso->enrolledStudies->sortByDesc('created_at')->first();
        $studyDetails = null;
        if ($latestEnrollment && $latestEnrollment->study) {
            $studyDetails = [
                'name' => $latestEnrollment->study->name,
                'description' => $latestEnrollment->study->description_1,
                'created_at' => $latestEnrollment->start_date,
                'end_date' => $latestEnrollment->end_date ? $latestEnrollment->end_date : null,
            ];
        }

        // $perso = $user->perso;
        // $currentStudyEnrollment = $perso->enrolledStudies()->latest('start_date')->first();

        // if ($currentStudyEnrollment) {
        //     $currentStudy = $currentStudyEnrollment->study;
        //     // Calculez ici le temps restant et d'autres données nécessaires
        // } else {
        //     $currentStudy = null;
        //     // Gérez le cas où il n'y a pas d'étude en cours
        // }

        return Inertia::render('Study/Current', [
            // 'currentStudy' => $currentStudy ? $currentStudy->toArray() : null,
            'studyDetails' => $studyDetails,

        ]);
    }
}
