<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function purchase(Request $request)
    {
        $user = Auth::user();

        if (!$user->perso) {
            return redirect()->back()->withErrors('Vous devez avoir un perso pour effectuer un achat.');
        }

        $request->validate([
            'productId' => 'required|integer|exists:items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $perso = $user->perso;
        $product = Item::findOrFail($request->productId);
        $totalPrice = $product->price * $request->quantity;

        if ($perso->money < $totalPrice) {
            return redirect()->back()->withErrors('Vous n’avez pas suffisamment d’argent pour cet achat.');
        }

        DB::beginTransaction();
        try {
            // Déduire le montant de l'argent du perso
            $perso->money -= $totalPrice;
            $perso->save();

            // Récupérer l'inventaire existant du personnage
            $inventory = $perso->inventory;

            // Vérifier si l'article existe déjà dans l'inventaire
            $inventoryItem = $inventory->items()->where('items_id', $product->id)->first();

            if ($inventoryItem) {
                // Si l'article existe déjà, augmenter la quantité dans la table pivot
                $currentQuantity = $inventoryItem->pivot->quantity;
                $inventory->items()->updateExistingPivot($product->id, ['quantity' => $currentQuantity + $request->quantity]);
            } else {
                // Sinon, ajouter l'article à l'inventaire avec la quantité spécifiée
                $inventory->items()->attach($product->id, ['quantity' => $request->quantity]);
            }


            DB::commit();
            return redirect()->route('city.lifemarket')->with('message', 'Achat réussi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Une erreur est survenue lors de l’achat.');
        }
    }
}
