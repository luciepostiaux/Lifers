<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateLifeGaugesFromSubscriptions extends Command
{
    protected $signature = 'update:life-gauges-from-subscriptions';
    protected $description = 'Renouvelle les abonnements et met à jour les jauges de vie en fonction des sessions de sport';

    public function handle()
    {
        $today = Carbon::today();
        $subscriptions = Subscription::where('status', 'active')
            ->with(['sportSession', 'perso.lifeGauge'])
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->end_date->isToday()) {
                // Réinitialiser les dates de l'abonnement
                $subscription->update([
                    'start_date' => $today,
                    'end_date' => $today->copy()->addDays($subscription->sportSession->duration),
                ]);

                // Augmenter les jauges de vie du personnage lié
                if ($subscription->perso && $subscription->perso->lifeGauge) {
                    $newPhysicalCondition = min(100, $subscription->perso->lifeGauge->physical_condition + $subscription->sportSession->effect);
                    $subscription->perso->lifeGauge->update([
                        'physical_condition' => $newPhysicalCondition,
                    ]);
                }

                $this->info("L'abonnement {$subscription->name} pour le perso {$subscription->perso_id} a été renouvelé et la jauge de condition physique a été augmentée.");
            }
        }
    }
}
