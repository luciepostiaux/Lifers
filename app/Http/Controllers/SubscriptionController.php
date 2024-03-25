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
        $user = Auth::user();
        $perso = $user->perso;
        $subscriptionName = $request->input('subscriptionName');
        $sportSession = SportSession::where('name', $subscriptionName)->firstOrFail();

        // Vérifier s'il y a un abonnement actif différent
        $activeSubscription = $perso->subscriptions()->where('type', 'gym')->where('status', 'active')->first();
        if ($activeSubscription && $activeSubscription->name != $subscriptionName) {
            // Désactiver l'abonnement actif
            $activeSubscription->update(['status' => 'cancelled']);
        }

        // Réactiver ou créer un abonnement
        $subscription = $perso->subscriptions()->where('type', 'gym')->where('name', $subscriptionName)->first();
        if ($subscription) {
            $subscription->update([
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($sportSession->duration),
                'status' => 'active'
            ]);
        } else {
            Subscription::create([
                'perso_id' => $perso->id,
                'type' => 'gym',
                'name' => $subscriptionName,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($sportSession->duration),
                'status' => 'active'
            ]);
        }

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
