<?php

namespace App\Http\Controllers;

use App\Models\LifeGauge;
use App\Models\Perso;
use App\Models\PersoBody;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CharacterController extends Controller
{
    public function create()
    {
        $persoBodies = PersoBody::all();

        return Inertia::render('Character/Create', [
            'persoBodies' => $persoBodies
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'perso_bodies_id' => 'required|exists:perso_bodies,id',
        ]);

        $existingPerso = Auth::user()->perso;

        if ($existingPerso) {
            return redirect()->back()->with('error', 'Vous avez déjà un personnage actif.');
        }

        $perso = new Perso();
        $perso->first_name = $request->first_name;
        $perso->last_name = $request->last_name;
        $perso->perso_bodies_id = $request->perso_bodies_id;
        $perso->user_id = Auth::id();
        $perso->birth_date = Carbon::today()->toDateString();
        $perso->money = 900;
        $perso->hairstyles_id = null;
        $perso->jobs_id = null;
        $perso->mother_id = null;
        $perso->father_id = null;

        $perso->save();


        $lifeGauges = new LifeGauge([
            'perso_id' => $perso->id,
            'hunger' => 100,
            'thirst' => 100,
            'clean' => 100,
            'happiness' => 100,
            'health' => 100,
        ]);

        $lifeGauges->save();

        return redirect()->route('dashboard')->with('success', 'Personnage créé avec succès.');
    }
}
