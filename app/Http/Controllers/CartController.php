<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\LiferGameState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'productId' => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $lifer = $this->activeLifer(['inventory']);
        $item = Item::findOrFail($validated['productId']);
        $totalCents = (int) round((float) $item->price * 100) * $validated['quantity'];
        $totalPrice = number_format($totalCents / 100, 2, '.', '');

        DB::transaction(function () use ($lifer, $item, $validated, $totalPrice) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $unitsToAdd = $validated['quantity'] * $item->units_per_purchase;

            $availableCents = (int) round((float) $state->money * 100);
            if ($availableCents < (int) round((float) $totalPrice * 100)) {
                throw ValidationException::withMessages([
                    'productId' => 'Vous n’avez pas suffisamment d’argent pour cet achat.',
                ]);
            }

            $existing = DB::table('inventory_items')
                ->where('inventory_id', $lifer->id)
                ->where('item_id', $item->id)
                ->lockForUpdate()
                ->first();

            $state->decrement('money', $totalPrice);

            if ($existing) {
                DB::table('inventory_items')
                    ->where('inventory_id', $lifer->id)
                    ->where('item_id', $item->id)
                    ->update([
                        'quantity' => $existing->quantity + $unitsToAdd,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('inventory_items')->insert([
                    'inventory_id' => $lifer->id,
                    'item_id' => $item->id,
                    'quantity' => $unitsToAdd,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('city.lifemarket')->with('message', 'Achat réussi.');
    }
}
