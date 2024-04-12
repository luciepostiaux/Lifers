<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FamilyController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perso = $user->perso()->with(['lifeGauge', 'inventory.items.effects', 'sicknesses'])->first();

        $bodyImageUrl = $perso && $perso->body ? $perso->body->img_perso : null;

        return Inertia::render('AtHome/Index', [
            'perso' => $perso ? $perso->toArray() : null,
            'bodyImageUrl' => $bodyImageUrl,

            // 'age' => $perso ? $perso->calculateAge() : null,

        ]);
    }
}
