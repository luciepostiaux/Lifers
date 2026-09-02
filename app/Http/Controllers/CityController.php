<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DailyJournalAccess;
use App\Models\FamilyChild;
use App\Models\Item;
use App\Models\LiferGameState;
use App\Models\SportSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CityController extends Controller
{
    public function index()
    {
        $lifer = $this->activeLifer([
            'gameState',
            'lifeGauge',
            'inventory.items',
            'sicknesses',
            'subscriptions.sportSession',
            'activeStudyEnrollment.study',
            'employment.job',
        ]);

        $activeSubscription = $lifer->subscriptions->first(
            fn ($subscription) => $subscription->status === 'active'
                && $subscription->ends_at->isFuture(),
        );

        return Inertia::render('City/Index', [
            'money' => $lifer->gameState?->money,
            'cityStatus' => [
                'inventory_quantity' => $lifer->inventory?->items
                    ->sum(fn ($item) => $item->pivot->quantity) ?? 0,
                'market_items_count' => Item::count(),
                'health' => $lifer->lifeGauge?->health,
                'sickness_count' => $lifer->sicknesses->count(),
                'activities_count' => Activity::count(),
                'sport_options_count' => SportSession::count(),
                'active_subscription' => $activeSubscription?->sportSession?->name,
                'current_study' => $lifer->activeStudyEnrollment?->study?->name,
                'current_job' => $lifer->employment?->job?->name,
                'orphan_count' => FamilyChild::query()->where('status', FamilyChild::STATUS_ORPHANED)->count(),
                'has_daily_journal_access' => DailyJournalAccess::query()
                    ->where('lifer_id', $lifer->id)
                    ->whereDate('access_date', today())
                    ->exists(),
            ],
        ]);
    }

    public function lifeMarket()
    {
        $lifer = $this->activeLifer(['gameState', 'inventory.items']);
        $quantities = $lifer->inventory->items->pluck('pivot.quantity', 'id');

        $products = Item::with('effects')
            ->get()
            ->sortBy(fn (Item $item) => sprintf('%02d-%s', Item::categoryRank($item->category), $item->name))
            ->values()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'units_per_purchase' => $item->units_per_purchase,
                'category' => $item->category,
                'image_path' => $item->image_path,
                'background_image_path' => $item->background_image_path,
                'inventory_quantity' => $quantities->get($item->id, 0),
                'effects' => $item->effects->map(fn ($effect) => [
                    'gauge' => $effect->gauge,
                    'effect' => $effect->effect,
                ])->values(),
            ]);

        return Inertia::render('City/LifeMarket', [
            'productsByCategory' => $products->groupBy('category'),
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function sport()
    {
        $lifer = $this->activeLifer([
            'gameState',
            'lifeGauge',
            'subscriptions.sportSession',
        ]);

        return Inertia::render('City/Sport', [
            'singleSession' => SportSession::where('type', 'single')->first(),
            'sportSessions' => SportSession::where('type', 'gym')->get(),
            'activeSubscription' => $lifer->subscriptions
                ->first(fn ($subscription) => $subscription->status === 'active' && $subscription->ends_at->isFuture()),
            'physicalCondition' => $lifer->lifeGauge?->physical_condition,
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function buySingleSportSession(Request $request)
    {
        $validated = $request->validate([
            'sessionId' => ['required', 'integer', 'exists:sport_sessions,id'],
        ]);

        $lifer = $this->activeLifer();
        $session = SportSession::whereKey($validated['sessionId'])->where('type', 'single')->firstOrFail();

        DB::transaction(function () use ($lifer, $session) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $lifeGauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();

            if ($state->money < $session->price) {
                throw ValidationException::withMessages([
                    'sessionId' => 'Vous n’avez pas assez d’argent pour cette séance.',
                ]);
            }

            $state->decrement('money', $session->price);
            $state->update(['last_sport_activity_on' => today()]);
            $lifeGauge->update([
                'physical_condition' => min(
                    100,
                    $lifeGauge->physical_condition + $session->physical_condition_effect,
                ),
            ]);
        });

        return back()->with('success', 'Séance de sport achetée avec succès.');
    }

    public function entertainment()
    {
        $lifer = $this->activeLifer(['gameState', 'lifeGauge']);

        return Inertia::render('City/Entertainment', [
            'activitiesByCategory' => Activity::with('effects')->get()->groupBy('category'),
            'lifeGauges' => [
                'happiness' => $lifer->lifeGauge?->happiness,
                'entertainment' => $lifer->lifeGauge?->entertainment,
                'physical_condition' => $lifer->lifeGauge?->physical_condition,
            ],
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function participateInActivity(Request $request)
    {
        $validated = $request->validate([
            'activityId' => ['required', 'integer', 'exists:activities,id'],
        ]);

        $lifer = $this->activeLifer();
        $activity = Activity::with('effects')->findOrFail($validated['activityId']);

        DB::transaction(function () use ($lifer, $activity) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);
            $lifeGauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();

            if ($state->money < $activity->price) {
                throw ValidationException::withMessages([
                    'activityId' => 'Vous n’avez pas assez d’argent pour cette activité.',
                ]);
            }

            $state->decrement('money', $activity->price);

            foreach ($activity->effects as $effect) {
                $gauge = $effect->gauge;
                $lifeGauge->{$gauge} = max(0, min(100, $lifeGauge->{$gauge} + $effect->effect));
            }

            $lifeGauge->save();
        });

        return back()->with('success', 'Vous avez participé à l’activité avec succès.');
    }

    public function doctor()
    {
        $lifer = $this->activeLifer(['gameState', 'lifeGauge', 'sicknesses']);

        return Inertia::render('City/Doctor', [
            'currentSicknesses' => $lifer->sicknesses,
            'health' => $lifer->lifeGauge?->health,
            'doctorVisitCost' => 150,
            'money' => $lifer->gameState?->money,
        ]);
    }
}
