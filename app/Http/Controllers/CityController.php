<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Item;
use App\Models\SportSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CityController extends Controller
{
    public function index()
    {
        return Inertia::render('City/Index');
    }
    public function lifeMarket()
    {
        $user = auth()->user();


        $products = Item::all()->map(function ($item) use ($user) {
            $inventoryQuantity = 0;
            if ($user && $user->perso && $user->perso->inventory) {
                $inventoryItem = $user->perso->inventory->items->where('id', $item->id)->first();
                $inventoryQuantity = $inventoryItem ? $inventoryItem->pivot->quantity : 0;
            }
            $item->inventoryQuantity = $inventoryQuantity;
            return $item;
        });

        $productsByCategory = $products->groupBy('category');

        return Inertia::render('City/LifeMarket', [
            'productsByCategory' => $productsByCategory,
        ]);
    }
    public function sport()
    {
        $user = Auth::user();
        $perso = $user->perso;
        $activeSubscription = $perso->subscriptions()->where('type')->where('end_date', '>', Carbon::now())->first();
        $sportSessions = SportSession::all(); // Assurez-vous que ceci existe déjà

        return Inertia::render('City/Sport', [
            'sportSessions' => $sportSessions,
            'activeSubscription' => $activeSubscription, // Ajoutez ceci
        ]);
    }


    public function buySingleSportSession(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;

        $sessionPrice = 40;
        $sessionEffect = 15;

        // Vérifier si le personnage a suffisamment d'argent pour la séance
        if ($perso->money < $sessionPrice) {
            return redirect()->back()->withErrors('Vous n\'avez pas assez d\'argent pour cette séance.');
        }

        // Déduire le prix de la séance de l'argent du personnage
        $perso->decrement('money', $sessionPrice);

        // Mettre à jour la jauge de condition physique du personnage
        $currentPhysicalCondition = $perso->lifeGauge->physical_condition;
        $newPhysicalCondition = max(0, min(100, $currentPhysicalCondition + $sessionEffect)); // Garde la valeur entre 0 et 100
        $perso->lifeGauge->update(['physical_condition' => $newPhysicalCondition]);

        // Sauvegarder les changements de la jauge de vie
        $perso->lifeGauge->save();

        return redirect()->back()->with('success', 'Séance de sport achetée avec succès.');
    }


    public function entertainment()
    {
        $activities = Activity::all();
        $activitiesByCategory = $activities->groupBy('category');

        return Inertia::render('City/Entertainment', [
            'activitiesByCategory' => $activitiesByCategory,
        ]);
    }

    public function participateInActivity(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $activity = Activity::findOrFail($request->activityId);

        // Vérifier si l'utilisateur a suffisamment d'argent pour participer à l'activité
        if ($perso->money < $activity->price) {
            return redirect()->back()->withErrors('Vous n\'avez pas assez d\'argent pour cette activité.');
        }

        // Déduire le prix de l'activité de l'argent du personnage
        $perso->decrement('money', $activity->price);

        // Accéder aux effets de l'activité et les appliquer à la jauge correspondante du personnage
        $effects = $activity->effects; // Assurez-vous que votre modèle Activity est bien configuré pour retourner les effets
        foreach ($effects as $effect) {
            $gauge = $effect->effect_type;
            $value = $effect->effect_value;

            // Appliquer l'effet à la jauge correspondante, en vous assurant de ne pas dépasser les limites
            if (in_array($gauge, ['hunger', 'thirst', 'clean', 'happiness', 'health', 'entertainment'])) {
                $currentValue = $perso->lifeGauge->{$gauge};
                $newValue = max(0, min(100, $currentValue + $value)); // Garde la valeur entre 0 et 100
                $perso->lifeGauge->{$gauge} = $newValue;
            }
        }

        $perso->lifeGauge->save(); // Sauvegarder les changements de la jauge de vie

        return redirect()->back()->with('success', 'Vous avez participé à l\'activité avec succès.');
    }
}
