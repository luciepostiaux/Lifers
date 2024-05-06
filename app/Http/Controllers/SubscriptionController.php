<?php

namespace App\Http\Controllers;

use App\Models\SportSession;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function subscribeToGym(Request $request)
    {
        // Récupérer l'utilisateur authentifié et son personnage
        $user = Auth::user();
        $perso = $user->perso;
        $subscriptionName = $request->input('subscriptionName');

        // Récupérer la session sportive correspondante
        $sportSession = SportSession::where('name', $subscriptionName)->firstOrFail();

        // Vérifier si le personnage a assez de fonds
        $price = $sportSession->price;
        if ($perso->money < $price) {
            return redirect()->back()->withErrors('Fonds insuffisants pour cet abonnement.');
        }

        // Rechercher l'abonnement actif actuel
        $activeSubscription = $perso->subscriptions()
            ->where('type', 'gym')
            ->where('status', 'active')
            ->first();

        // Désactiver l'abonnement actif s'il est différent du nouveau
        if ($activeSubscription && $activeSubscription->name !== $subscriptionName) {
            $activeSubscription->update(['status' => 'cancelled']);
        }

        // Trouver ou créer un nouvel abonnement
        $subscription = $perso->subscriptions()
            ->where('type', 'gym')
            ->where('name', $subscriptionName)
            ->first();

        if ($subscription) {
            // Réactiver l'abonnement existant
            $subscription->update([
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($sportSession->duration),
                'status' => 'active'
            ]);
        } else {
            // Créer un nouvel abonnement
            Subscription::create([
                'perso_id' => $perso->id,
                'type' => 'gym',
                'name' => $subscriptionName,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($sportSession->duration),
                'status' => 'active'
            ]);
        }

        // Déduire le coût du solde du personnage
        $perso->money -= $price;
        $perso->save();

        // Retourner avec un message de succès
        return redirect()->back()->with('message', 'Abonnement à la salle de sport réussi.');
    }



    public function cancelGymSubscription()
    {
        $user = Auth::user();
        $perso = $user->perso;

        $activeSubscription = $perso->subscriptions()->where('end_date', '>', now())->first();
        if (!$activeSubscription) {
            return redirect()->back()->withErrors(['msg' => 'Aucun abonnement actif à annuler.']);
        }
        DB::beginTransaction();
        try {
            $activeSubscription->update([
                'end_date' => now(), // Mettre fin immédiatement à l'abonnement
                'status' => 'cancelled',
            ]);

            DB::commit();
            return redirect()->back()->with('message', 'Votre abonnement a été annulé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors de l’annulation de l’abonnement.']);
        }
    }
}
