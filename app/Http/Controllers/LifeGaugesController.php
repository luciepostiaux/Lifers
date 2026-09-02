<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LifeGaugesController extends Controller
{
    public function index()
    {
        $lifeGauge = $this->activeLifer(['lifeGauge'])->lifeGauge;

        return Inertia::render('LifeGauges/Index', [
            'lifeGauges' => [
                'Faim' => $lifeGauge->hunger,
                'Soif' => $lifeGauge->thirst,
                'Propreté' => $lifeGauge->clean,
                'Bonheur' => $lifeGauge->happiness,
                'Divertissement' => $lifeGauge->entertainment,
                'Condition physique' => $lifeGauge->physical_condition,
                'Santé' => $lifeGauge->health,
            ],
        ]);
    }
}
