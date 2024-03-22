<?php

namespace App\Http\Controllers;

use App\Models\Perso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LifeGaugesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perso = $user->perso()->with(['lifeGauge',])->first();

        $lifeGauges = null;
        dd($lifeGauges);
        if ($perso && $perso->lifeGauge) {
            $lifeGauge = $perso->lifeGauge;
            $lifeGauges = [
                'Faim' => $lifeGauge->hunger,
                'Soif' => $lifeGauge->thirst,
                'Propreté' => $lifeGauge->clean,
                'Bonheur' => $lifeGauge->happiness,
                'Divertissement' => $lifeGauge->entertainment,
                'Condition physique' => $lifeGauge->physical_condition,
                'Santé' => $lifeGauge->health,
            ];
        }
        return Inertia::render('LifeGauges/Index', [
            'lifeGauges' => $lifeGauges,
        ]);
    }
}
