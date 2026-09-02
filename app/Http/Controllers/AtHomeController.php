<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\LiferItemUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AtHomeController extends Controller
{
    public function index()
    {
        $lifer = $this->activeLifer([
            'gameState.bodyType',
            'lifeGauge',
            'inventory.items.effects',
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

        $inventoryItemsByCategory = [];
        $inventoryItems = collect($lifer->inventory?->items ?? [])
            ->sortBy(fn (Item $item) => sprintf('%02d-%s', Item::categoryRank($item->category), $item->name));

        foreach ($inventoryItems as $item) {
            $inventoryItemsByCategory[$item->category][] = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->pivot->quantity,
                'category' => $item->category,
                'image_path' => $item->image_path,
                'background_image_path' => $item->background_image_path,
                'effects' => $item->effects->map(fn ($effect) => [
                    'gauge' => $effect->gauge,
                    'effect' => $effect->effect,
                ])->values(),
            ];
        }

        $currentSicknesses = $lifer->sicknesses->map(fn ($sickness) => [
            'id' => $sickness->id,
            'name' => $sickness->name,
            'description' => $sickness->description,
            'contracted_at' => $sickness->pivot->contracted_at,
            'expected_recovery_at' => $sickness->pivot->expected_recovery_at,
            'fatal_at' => $sickness->pivot->fatal_at,
            'needs_doctor' => $sickness->needs_doctor,
            'self_resolving' => $sickness->self_resolving,
            'treatment_cost' => $sickness->treatment_cost,
        ]);

        return Inertia::render('AtHome/Index', [
            'lifer' => [
                'first_name' => $lifer->first_name,
                'last_name' => $lifer->last_name,
            ],
            'bodyImageUrl' => $lifer->gameState?->bodyType?->image_path,
            'money' => $lifer->gameState?->money,
            'lifeGauges' => $lifeGauges,
            'inventoryItemsByCategory' => $inventoryItemsByCategory,
            'currentSicknesses' => $currentSicknesses,
        ]);
    }

    public function consumeItem(Request $request)
    {
        $validated = $request->validate([
            'itemId' => ['required', 'integer', 'exists:items,id'],
        ]);

        $lifer = $this->activeLifer();

        DB::transaction(function () use ($lifer, $validated) {
            $inventoryItem = DB::table('inventory_items')
                ->where('inventory_id', $lifer->id)
                ->where('item_id', $validated['itemId'])
                ->lockForUpdate()
                ->first();

            if (! $inventoryItem || $inventoryItem->quantity < 1) {
                throw ValidationException::withMessages([
                    'itemId' => 'L’article demandé n’est pas dans votre inventaire.',
                ]);
            }

            $item = Item::with('effects')->findOrFail($validated['itemId']);
            $lifeGauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();

            foreach ($item->effects as $effect) {
                $gauge = $effect->gauge;
                $lifeGauge->{$gauge} = max(0, min(100, $lifeGauge->{$gauge} + $effect->effect));
            }

            $lifeGauge->save();

            LiferItemUsage::create([
                'lifer_id' => $lifer->id,
                'item_id' => $item->id,
                'usage_tag' => $item->usage_tag,
                'quantity' => 1,
                'used_at' => now(),
            ]);

            if ($inventoryItem->quantity > 1) {
                DB::table('inventory_items')
                    ->where('inventory_id', $lifer->id)
                    ->where('item_id', $validated['itemId'])
                    ->update([
                        'quantity' => $inventoryItem->quantity - 1,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('inventory_items')
                    ->where('inventory_id', $lifer->id)
                    ->where('item_id', $validated['itemId'])
                    ->delete();
            }
        });

        return back()->with('success', 'Objet consommé avec succès.');
    }
}
