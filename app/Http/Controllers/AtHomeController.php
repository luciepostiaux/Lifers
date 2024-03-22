<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AtHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perso = $user->perso()->with(['lifeGauge', 'inventory.items.effects'])->first();

        $lifeGauges = null;
        $inventoryItemsByCategory = [];

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
        if ($perso && $perso->inventory) {
            $items = $perso->inventory->items()->withPivot('quantity')->get();

            foreach ($items as $item) {
                $inventoryItemsByCategory[$item->category][] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity,
                    'category' => $item->category, // Assurez-vous que vos articles ont une catégorie
                ];
            }
        }


        return Inertia::render('AtHome/Index', [
            'lifeGauges' => $lifeGauges,
            'inventoryItemsByCategory' => $inventoryItemsByCategory,
        ]);
    }

    public function consumeItem(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $inventory = $perso->inventory;
        // Recherche de l'article dans l'inventaire par l'ID de l'article
        $item = $inventory->items()->wherePivot('items_id', $request->itemId)->first();



        // Vérifier si l'article existe dans l'inventaire
        if (!$item) {
            return redirect()->back()->withErrors('L\'article demandé n\'est pas dans l\'inventaire.');
        }

        // Accéder aux effets de l'article et appliquer chaque effet à la jauge correspondante du personnage
        foreach ($item->effects as $effect) {
            $gauge = $effect->gauge;
            $value = $effect->effect;

            // Appliquer l'effet à la jauge correspondante, en vous assurant de ne pas dépasser les limites
            if (in_array($gauge, ['hunger', 'thirst', 'clean', 'happiness', 'health', 'entertainment', 'physical_condition'])) {
                $currentValue = $perso->lifeGauge->{$gauge};
                $newValue = max(0, min(100, $currentValue + $value)); // Garde la valeur entre 0 et 100
                $perso->lifeGauge->{$gauge} = $newValue;
            }
        }

        $perso->lifeGauge->save();

        // Diminuer la quantité de l'article dans l'inventaire et sauvegarder
        $currentQuantity = $item->pivot->quantity;
        if ($currentQuantity > 1) {
            // Si plus d'un, décrémentez la quantité
            $inventory->items()->updateExistingPivot($item->id, ['quantity' => $currentQuantity - 1]);
        } else {
            // Si la quantité est de un, supprimez l'article de l'inventaire
            $inventory->items()->detach($item->id);
        }

        return redirect()->back()->with('success', 'Item consommé avec succès.');
    }
}
