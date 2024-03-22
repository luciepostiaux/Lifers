<?php

namespace App\Http\Controllers;

use App\Models\SportSession;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function subscribeToGym(Request $request)
    {
        $user = Auth::user();
        $perso = $user->perso;
        $subscriptionType = $request->input('subscriptionType'); // 'daily', 'premium', etc.
        // Trouvez les détails de la séance de sport basée sur le type
        $sportSession = SportSession::where('type', $subscriptionType)->firstOrFail();

        // Vérifiez si l'utilisateur a déjà un abonnement actif
        $activeSubscription = $perso->subscriptions()->where('type', 'gym')->where('end_date', '>', Carbon::now())->first();

        if ($activeSubscription) {
            return redirect()->back()->withErrors('Vous avez déjà un abonnement actif à la salle de sport.');
        }

        // Vérifiez si l'utilisateur a suffisamment d'argent
        if ($perso->money >= $sportSession->price) {
            // Déduisez le coût de l'abonnement de l'argent du personnage
            $perso->decrement('money', $sportSession->price);
            $perso->save();

            // Créez ou mettez à jour un abonnement
            $subscription = Subscription::updateOrCreate(
                ['perso_id' => $perso->id, 'type' => 'gym'],
                [
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays($sportSession->duration),
                    'status' => 'active'
                ]
            );

            return redirect()->back()->with('success', 'Abonnement à la salle de sport réussi.');
        } else {
            return redirect()->back()->withErrors('Vous n\'avez pas assez d\'argent pour cet abonnement.');
        }
    }
}
