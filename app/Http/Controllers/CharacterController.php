<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\LifeGauge;
use App\Models\Perso;
use App\Models\PersoBody;
use App\Models\Residence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $residence = Residence::where('type', 'Caravane rouillée')->first();

        if (!$residence) {
            return redirect()->back()->with('error', 'La résidence de type "Caravane rouillée" n\'existe pas.');
        }

        DB::transaction(function () use ($request, $residence) {
            $perso = Perso::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'perso_bodies_id' => $request->perso_bodies_id,
                'user_id' => Auth::id(),
                'birth_date' => Carbon::today()->toDateString(),
                'money' => 900,
            ]);

            Inventory::create(['perso_id' => $perso->id]);

            LifeGauge::create([
                'perso_id' => $perso->id,
                'hunger' => 100,
                'thirst' => 100,
                'clean' => 100,
                'happiness' => 100,
                'entertainment' => 100,
                'health' => 100,
                'physical_condition' => 100,
            ]);

            $perso->residences()->attach($residence->id, ['active' => true]);
        });

        return redirect()->route('dashboard')->with('success', 'Personnage créé avec succès.');
    }
}
