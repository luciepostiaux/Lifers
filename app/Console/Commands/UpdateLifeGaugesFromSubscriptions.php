<?php

namespace App\Console\Commands;

use App\Models\LiferGameState;
use App\Models\LiferSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLifeGaugesFromSubscriptions extends Command
{
    protected $signature = 'update:life-gauges-from-subscriptions';

    protected $description = 'Renouvelle les abonnements sportifs arrivés à échéance';

    public function handle(): int
    {
        LiferSubscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->with(['sportSession', 'lifer.lifeGauge'])
            ->each(function (LiferSubscription $subscription) {
                DB::transaction(function () use ($subscription) {
                    $locked = LiferSubscription::query()->lockForUpdate()->findOrFail($subscription->id);
                    $state = LiferGameState::query()->lockForUpdate()->findOrFail($locked->lifer_id);

                    if ($locked->status !== 'active' || $locked->ends_at->isFuture()) {
                        return;
                    }

                    if ($state->money < $subscription->sportSession->price) {
                        $locked->update(['status' => 'expired']);

                        return;
                    }

                    $state->decrement('money', $subscription->sportSession->price);
                    $state->update(['last_sport_activity_on' => today()]);
                    $locked->update([
                        'starts_at' => now(),
                        'ends_at' => now()->addDays($subscription->sportSession->duration_days),
                    ]);

                    $gauge = $subscription->lifer->lifeGauge()->lockForUpdate()->firstOrFail();
                    $gauge->update([
                        'physical_condition' => min(
                            100,
                            $gauge->physical_condition + $subscription->sportSession->physical_condition_effect,
                        ),
                    ]);
                });
            });

        return self::SUCCESS;
    }
}
