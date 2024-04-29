<?php

namespace App\Http\Controllers;

use App\Models\Perso;
use App\Models\Residence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResidencePaymentController extends Controller
{
    public function buy(Request $request)
    {
        $perso = Perso::where('user_id', $request->user()->id)->first();
        $residence = Residence::find($request->residenceId);
        $paymentType = $request->paymentType;

        if (!$residence) {
            return redirect()->back()->withErrors(['error' => 'Logement non trouvé.']);
        }

        // Vérification si la résidence est déjà possédée
        if ($perso->residences()->where('residence_id', $residence->id)->exists()) {
            return redirect()->back()->withErrors(['error' => 'Vous possédez déjà cette résidence.']);
        }

        if ($paymentType == 'full' && $perso->money < $residence->prix_achat) {
            return redirect()->back()->withErrors(['error' => 'Fonds insuffisants pour cet achat.']);
        }

        DB::beginTransaction();
        try {
            if ($paymentType == 'full') {
                $perso->money -= $residence->prix_achat;
                $perso->residences()->attach($residence->id, ['active' => false]);
            } else if ($paymentType == 'installment') {
                $montantMensuel = ($residence->prix_achat + $residence->taxe_mensuelle * 3) / 3;
                if ($perso->money < $montantMensuel) {
                    return redirect()->back()->withErrors(['error' => 'Fonds insuffisants pour le premier paiement.']);
                }
                $perso->money -= $montantMensuel;

                $perso->residences()->attach($residence->id, [
                    'active' => false,
                    'remaining_payments' => 2,
                    'next_payment_due' => now()->addDay()
                ]);
            }
            $perso->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de la transaction.']);
        }

        return redirect()->back()->with('success', 'Achat de logement réussi.');
    }
}
