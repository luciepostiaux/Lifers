<?php

namespace App\Http\Controllers;

use App\Models\FamilyChild;
use App\Services\FamilyLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrphanageController extends Controller
{
    public function index(): Response
    {
        $lifer = $this->activeLifer(['gameState']);
        $marriage = $lifer->activeMarriage();
        $marriage?->load(['firstLifer:id,first_name,last_name,status', 'secondLifer:id,first_name,last_name,status']);
        $spouse = $marriage?->spouseOf($lifer);
        $adopterIds = collect([$lifer->id, $spouse?->id])->filter()->unique()->values();
        $adoptionCounts = DB::table('family_child_guardians')
            ->whereIn('lifer_id', $adopterIds)
            ->whereNotNull('adopted_at')
            ->where('adopted_at', '>=', now()->subDays(FamilyLifecycleService::ADOPTION_PERIOD_DAYS))
            ->selectRaw('lifer_id, COUNT(*) AS total')
            ->groupBy('lifer_id')
            ->pluck('total', 'lifer_id');

        $remaining = $adopterIds
            ->map(fn (int $id) => max(0, FamilyLifecycleService::ADOPTION_LIMIT - (int) ($adoptionCounts[$id] ?? 0)))
            ->min() ?? FamilyLifecycleService::ADOPTION_LIMIT;

        $children = FamilyChild::query()
            ->where('status', FamilyChild::STATUS_ORPHANED)
            ->whereNotNull('born_at')
            ->with('gauges')
            ->orderBy('born_at')
            ->get()
            ->filter(fn (FamilyChild $child) => $child->calculateAge() < 18)
            ->map(fn (FamilyChild $child) => [
                'id' => $child->id,
                'name' => trim($child->first_name.' '.$child->last_name),
                'age' => $child->calculateAge(),
                'gauges' => $child->gauges ? [
                    'hunger' => $child->gauges->hunger,
                    'hygiene' => $child->gauges->hygiene,
                    'affection' => $child->gauges->affection,
                ] : null,
            ])->values();

        return Inertia::render('City/Orphanage', [
            'money' => $lifer->gameState?->money,
            'children' => $children,
            'spouse' => $spouse ? [
                'id' => $spouse->id,
                'name' => trim($spouse->first_name.' '.$spouse->last_name),
            ] : null,
            'adoptionsRemaining' => $remaining,
        ]);
    }

    public function adopt(FamilyChild $child, FamilyLifecycleService $familyLifecycleService): RedirectResponse
    {
        $adopters = $familyLifecycleService->adoptChild($this->activeLifer(), $child);

        return to_route('city.orphanage')->with(
            'success',
            count($adopters) === 2
                ? 'L’enfant rejoint le foyer et les deux conjoints en partagent désormais la garde.'
                : 'L’enfant rejoint le foyer de ton Lifer.',
        );
    }
}
