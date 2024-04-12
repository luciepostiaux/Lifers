<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Item;
use App\Models\Sickness;
use App\Models\SportSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CityController extends Controller
{
    public function index()

    {
        $usersWithPerso = User::with(['perso' => function ($query) {
            $query->select(['id', 'first_name', 'last_name', 'birth_date', 'user_id']);
        }])
            ->orderBy('is_online', 'desc')
            ->get()
            ->map(function ($user) {
                if ($user->perso) {
                    $user->perso->age = $user->perso->calculateAge(); 
                }
                return $user;
            });

        return Inertia::render('City/Index', [
            'usersWithPerso' => $usersWithPerso,
        ]);
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
        $sportSessions = SportSession::all();
        $activeSubscription = null;

        if ($perso) {
            $activeSubscription = $perso->subscriptions()
                ->where('type', 'gym')
                ->where('end_date', '>', now())
                ->first();
        }

        return Inertia::render('City/Sport', [
            'sportSessions' => $sportSessions,
            'activeSubscription' => $activeSubscription,
        ]);
    }



    public function buySingleSportSession(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $sessionPrice = 40;
        $sessionEffect = 15;

        if ($perso->money < $sessionPrice) {
            return redirect()->back()->withErrors('Vous n\'avez pas assez d\'argent pour cette séance.');
        }

        $perso->decrement('money', $sessionPrice);
        $currentPhysicalCondition = $perso->lifeGauge->physical_condition;
        $newPhysicalCondition = max(0, min(100, $currentPhysicalCondition + $sessionEffect));
        $perso->lifeGauge->update(['physical_condition' => $newPhysicalCondition]);
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


        if ($perso->money < $activity->price) {
            return redirect()->back()->withErrors('Vous n\'avez pas assez d\'argent pour cette activité.');
        }

        $perso->decrement('money', $activity->price);
        $effects = $activity->effects;
        foreach ($effects as $effect) {
            $gauge = $effect->effect_type;
            $value = $effect->effect_value;


            if (in_array($gauge, ['hunger', 'thirst', 'clean', 'happiness', 'health', 'entertainment'])) {
                $currentValue = $perso->lifeGauge->{$gauge};
                $newValue = max(0, min(100, $currentValue + $value));
                $perso->lifeGauge->{$gauge} = $newValue;
            }
        }

        $perso->lifeGauge->save();

        return redirect()->back()->with('success', 'Vous avez participé à l\'activité avec succès.');
    }

    public function doctor()
    {
        $user = Auth::user();
        $perso = $user->perso;
        $allSicknesses = Sickness::all(); // Récupère toutes les maladies

        $currentSicknesses = $perso->sicknesses()->withPivot('created_at')->get();
        // dd($currentSicknesses);
        return Inertia::render('City/Doctor', [
            'currentSicknesses' => $currentSicknesses,
            'allSicknesses' => $allSicknesses,

        ]);
    }
}
