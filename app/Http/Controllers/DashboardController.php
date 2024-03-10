<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perso = $user->perso()->with(['lifeGauge', 'job', 'enrolledStudies.study'])->first();

        $money = $perso ? $perso->money : 0;
        $lifeGauges = null;
        if ($perso && $perso->lifeGauge) {
            $lifeGauge = $perso->lifeGauge;
            $lifeGauges = [
                'Faim' => $lifeGauge->hunger,
                'Soif' => $lifeGauge->thirst,
                'Propreté' => $lifeGauge->clean,
                'Bonheur' => $lifeGauge->happiness,
                'Santé' => $lifeGauge->health,
            ];
        }

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

        $jobDetails = $perso && $perso->job ? [
            'name' => $perso->job->name,
            'description' => $perso->job->description_1,
        ] : null;

        $bodyImageUrl = $perso && $perso->body ? $perso->body->img_perso : null;
        // dd($bodyImageUrl);
        return Inertia::render('Dashboard', [
            'perso' => $perso ? $perso->toArray() : null,
            'bodyImageUrl' => $bodyImageUrl,
            'money' => $money,
            'age' => $perso ? $perso->calculateAge() : null,
            'lifeGauges' => $lifeGauges,
            'studyDetails' => $studyDetails,
            'jobDetails' => $jobDetails,
        ]);
    }
}
