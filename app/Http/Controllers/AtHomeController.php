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
        $perso = $user->perso()->with([
            'lifeGauge',
            'inventory.items.effects',
            'sicknesses',
            'residences' => function ($query) {
                $query->withPivot('active');
            }
        ])->first();

        $lifeGauges = null;
        $inventoryItemsByCategory = [];
        $currentSicknesses = [];
        $money = $perso ? $perso->money : 0;

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
                    'description' => $item->description,

                    'quantity' => $item->pivot->quantity,
                    'category' => $item->category,
                    'img_item' => asset("{$item->img_item}"),
                ];
            }
        }
        if ($perso && $perso->sicknesses) {
            foreach ($perso->sicknesses as $sickness) {
                $currentSicknesses[] = [
                    'id' => $sickness->id,
                    'name' => $sickness->name,
                    'description' => $sickness->description,
                    'contracted_at' => $sickness->pivot->created_at,
                ];
            }
        }
        $bodyImageUrl = $perso && $perso->body ? $perso->body->img_perso : null;

        $residences = $perso->residences->map(function ($residence) {
            return [
                'id' => $residence->id,
                'type' => $residence->type,
                'image_path' => $residence->image_path,
                'active' => $residence->pivot->active,

            ];
        });

        $activeResidence = $residences->where('active', true)->first();
        $formattedMoney = $perso ? number_format($perso->money, 2, ',', ' ') : null;

        return Inertia::render('AtHome/Index', [
            'perso' => $perso ? $perso->toArray() : null,
            'bodyImageUrl' => $bodyImageUrl,
            'money' => $formattedMoney,
            'age' => $perso ? $perso->calculateAge() : null,
            'lifeGauges' => $lifeGauges,
            'inventoryItemsByCategory' => $inventoryItemsByCategory,
            'currentSicknesses' => $currentSicknesses,
            'residences' => $residences,
            'activeResidence' => $activeResidence,

        ]);
    }


    public function setActive($id)
    {
        $user = Auth::user();
        $perso = $user->perso;

        // Désactivez d'abord toutes les résidences actives
        $perso->residences()->update(['active' => false]);

        // Activez la nouvelle résidence
        $perso->residences()->updateExistingPivot($id, ['active' => true]);

        return redirect()->back()->with('success', 'La résidence a été activée avec succès.');
    }


    public function sellResidence(Request $request, $residenceId)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $residence = $perso->residences()->findOrFail($residenceId);
        $salePrice = round($residence->prix_achat * 0.7);

        $perso->money += $salePrice;
        $perso->save();

        $perso->residences()->detach($residenceId);

        return redirect()->back()->with('message', 'Résidence vendue pour ' . $salePrice . ' LC.');
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
