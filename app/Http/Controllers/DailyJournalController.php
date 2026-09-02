<?php

namespace App\Http\Controllers;

use App\Models\FamilyChild;
use App\Models\Lifer;
use App\Services\DailyJournalService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DailyJournalController extends Controller
{
    public function index(DailyJournalService $journal): Response|RedirectResponse
    {
        $lifer = $this->activeLifer(['gameState']);

        if (! $journal->hasAccess($lifer)) {
            return to_route('city')->withErrors([
                'journal' => 'Achète d’abord le journal du jour pour lire la nécrologie.',
            ]);
        }

        $liferDeaths = Lifer::query()
            ->where('status', Lifer::STATUS_DEAD)
            ->whereDate('died_at', today())
            ->get()
            ->map(fn (Lifer $deceased) => [
                'key' => "lifer-{$deceased->id}",
                'first_name' => $deceased->first_name,
                'last_name' => $deceased->last_name,
                'sex' => $deceased->sex,
                'age' => $deceased->age_at_death,
                'cause' => $deceased->death_cause,
                'died_at' => $deceased->died_at,
                'is_child' => false,
            ]);

        $childDeaths = FamilyChild::query()
            ->where('status', FamilyChild::STATUS_DEAD)
            ->whereDate('died_at', today())
            ->get()
            ->map(fn (FamilyChild $child) => [
                'key' => "child-{$child->id}",
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'sex' => $child->sex,
                'age' => $this->childAgeAtDeath($child),
                'cause' => $child->death_cause,
                'died_at' => $child->died_at,
                'is_child' => true,
            ]);

        return Inertia::render('City/DailyJournal', [
            'money' => $lifer->gameState?->money,
            'editionDate' => today()->toDateString(),
            'deaths' => $liferDeaths
                ->concat($childDeaths)
                ->sortByDesc('died_at')
                ->values(),
        ]);
    }

    public function purchase(DailyJournalService $journal): RedirectResponse
    {
        $journal->purchase($this->activeLifer());

        return to_route('city.journal.index')->with('success', 'Le journal du jour est maintenant disponible.');
    }

    private function childAgeAtDeath(FamilyChild $child): ?int
    {
        if (! $child->born_at || ! $child->died_at) {
            return null;
        }

        return min(18, intdiv((int) floor($child->born_at->diffInDays($child->died_at, true)), 3));
    }
}
