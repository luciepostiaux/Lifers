<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $lifer = $this->activeLifer([
            'gameState.bodyType',
            'lifeGauge',
            'employment.job',
            'activeStudyEnrollment.study',
            'sicknesses',
        ]);

        $lifeGauge = $lifer->lifeGauge;
        $lifeGauges = $lifeGauge ? [
            'Faim' => $lifeGauge->hunger,
            'Soif' => $lifeGauge->thirst,
            'Propreté' => $lifeGauge->clean,
            'Bonheur' => $lifeGauge->happiness,
            'Divertissement' => $lifeGauge->entertainment,
            'Condition physique' => $lifeGauge->physical_condition,
            'Santé' => $lifeGauge->health,
        ] : null;

        $enrollment = $lifer->activeStudyEnrollment;
        $studyDetails = $enrollment?->study ? [
            'name' => $enrollment->study->name,
            'description' => $enrollment->study->short_description,
            'created_at' => $enrollment->started_at,
            'end_date' => $enrollment->ends_at,
        ] : null;

        $job = $lifer->employment?->job;
        $jobDetails = $job ? [
            'name' => $job->name,
            'description' => $job->short_description,
        ] : null;

        $currentSicknesses = $lifer->sicknesses->map(fn ($sickness) => [
            'id' => $sickness->id,
            'name' => $sickness->name,
            'needs_doctor' => $sickness->needs_doctor,
            'self_resolving' => $sickness->self_resolving,
            'expected_recovery_at' => $sickness->pivot->expected_recovery_at,
            'fatal_at' => $sickness->pivot->fatal_at,
        ])->values();

        return Inertia::render('Dashboard', [
            'perso' => [
                'id' => $lifer->id,
                'first_name' => $lifer->first_name,
                'last_name' => $lifer->last_name,
            ],
            'bodyImageUrl' => $lifer->gameState->bodyType->image_path,
            'money' => $lifer->gameState->money,
            'age' => $lifer->calculateAge(),
            'lifeGauges' => $lifeGauges,
            'currentSicknesses' => $currentSicknesses,
            'studyDetails' => $studyDetails,
            'jobDetails' => $jobDetails,
        ]);
    }
}
