<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\Diploma;
use App\Models\Lifer;
use App\Models\Sickness;
use App\Services\LiferLifecycleService;
use App\Services\SicknessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminLiferController extends Controller
{
    private const GAUGES = [
        'hunger',
        'thirst',
        'clean',
        'happiness',
        'entertainment',
        'physical_condition',
        'health',
    ];

    public function show(Request $request, Lifer $lifer): Response
    {
        abort_unless($lifer->status === Lifer::STATUS_ACTIVE && $lifer->gameState()->exists(), 404);

        $lifer->load([
            'user:id,name,email',
            'gameState.bodyType:id,code,label,sex,image_path',
            'lifeGauge',
            'sicknesses:id,name,duration_days,fatal_after_days',
            'diplomas:id,name',
            'employment.job:id,name',
            'activeStudyEnrollment.study:id,name',
        ]);
        $adminLifer = $request->user()->activeLifer()->with('gameState')->first();

        return Inertia::render('Admin/Lifer', [
            'lifer' => [
                'id' => $lifer->id,
                'name' => "{$lifer->first_name} {$lifer->last_name}",
                'age' => $lifer->calculateAge(),
                'sex' => $lifer->sex,
                'account' => [
                    'name' => $lifer->user->name,
                    'email' => $lifer->user->email,
                ],
                'money' => $lifer->gameState->money,
                'gauges' => collect(self::GAUGES)->mapWithKeys(
                    fn (string $gauge) => [$gauge => $lifer->lifeGauge->{$gauge}],
                ),
                'sickness_ids' => $lifer->sicknesses->pluck('id')->values(),
                'sicknesses' => $lifer->sicknesses->map(fn (Sickness $sickness) => [
                    'id' => $sickness->id,
                    'name' => $sickness->name,
                    'contracted_at' => $sickness->pivot->contracted_at,
                    'expected_recovery_at' => $sickness->pivot->expected_recovery_at,
                    'fatal_at' => $sickness->pivot->fatal_at,
                ])->values(),
                'diplomas' => $lifer->diplomas->map(fn (Diploma $diploma) => [
                    'id' => $diploma->id,
                    'name' => $diploma->name,
                ])->values(),
                'job' => $lifer->employment?->job?->name,
                'study' => $lifer->activeStudyEnrollment?->study?->name,
            ],
            'sicknessCatalog' => Sickness::query()->orderBy('name')->get([
                'id',
                'name',
                'duration_days',
                'fatal_after_days',
            ]),
            'diplomaCatalog' => Diploma::query()->orderBy('name')->get(['id', 'name']),
            'money' => $adminLifer?->gameState?->money,
        ]);
    }

    public function updateMoney(Request $request, Lifer $lifer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'between:-10000000,10000000', 'not_in:0,0.0,0.00'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $lifer, $validated): void {
            $state = $this->activeLiferState($lifer);
            $before = (float) $state->money;
            $after = max(0, round($before + (float) $validated['amount'], 2));
            $state->update(['money' => $after]);

            $this->audit($request, 'lifer.money.updated', $lifer, [
                'before' => $before,
                'requested_adjustment' => (float) $validated['amount'],
                'after' => $after,
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'Le solde du Lifer a été mis à jour.');
    }

    public function updateGauges(Request $request, Lifer $lifer): RedirectResponse
    {
        $rules = ['reason' => ['required', 'string', 'min:3', 'max:1000']];
        foreach (self::GAUGES as $gauge) {
            $rules["gauges.{$gauge}"] = ['required', 'integer', 'between:0,100'];
        }
        $validated = $request->validate($rules);

        DB::transaction(function () use ($request, $lifer, $validated): void {
            $this->activeLiferState($lifer);
            $gauge = $lifer->lifeGauge()->lockForUpdate()->firstOrFail();
            $before = $gauge->only(self::GAUGES);
            $gauge->update($validated['gauges']);

            $this->audit($request, 'lifer.gauges.updated', $lifer, [
                'before' => $before,
                'after' => $gauge->fresh()->only(self::GAUGES),
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'Les jauges du Lifer ont été mises à jour.');
    }

    public function addSickness(
        Request $request,
        Lifer $lifer,
        SicknessService $sicknesses,
    ): RedirectResponse {
        $validated = $request->validate([
            'sickness_id' => ['required', 'integer', 'exists:sicknesses,id'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $sickness = Sickness::query()->findOrFail($validated['sickness_id']);

        DB::transaction(function () use ($request, $lifer, $sickness, $sicknesses, $validated): void {
            $this->activeLiferState($lifer);

            if (! $sicknesses->contract($lifer, $sickness)) {
                throw ValidationException::withMessages([
                    'sickness_id' => 'Ce Lifer possède déjà cette maladie.',
                ]);
            }

            $this->audit($request, 'lifer.sickness.added', $lifer, [
                'sickness_id' => $sickness->id,
                'sickness_name' => $sickness->name,
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'La maladie a été ajoutée au Lifer.');
    }

    public function removeSickness(Request $request, Lifer $lifer, Sickness $sickness): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $lifer, $sickness, $validated): void {
            $this->activeLiferState($lifer);
            $removed = $lifer->sicknesses()->detach($sickness->id);

            if (! $removed) {
                throw ValidationException::withMessages([
                    'sickness' => 'Ce Lifer ne possède pas cette maladie.',
                ]);
            }

            $this->audit($request, 'lifer.sickness.removed', $lifer, [
                'sickness_id' => $sickness->id,
                'sickness_name' => $sickness->name,
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'La maladie a été retirée du Lifer.');
    }

    public function kill(
        Request $request,
        Lifer $lifer,
        LiferLifecycleService $lifecycle,
    ): RedirectResponse {
        $validated = $request->validate([
            'cause' => ['required', 'string', 'min:3', 'max:255'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $lifer, $lifecycle, $validated): void {
            $lifecycle->die($lifer, $validated['cause']);
            $this->audit($request, 'lifer.killed', $lifer, [
                'death_cause' => $validated['cause'],
                'reason' => $validated['reason'],
            ]);
        });

        return to_route('admin.dashboard')->with(
            'success',
            'Le décès administratif a été enregistré et l’identité du Lifer a été conservée.',
        );
    }

    private function activeLiferState(Lifer $lifer)
    {
        if ($lifer->status !== Lifer::STATUS_ACTIVE) {
            abort(404);
        }

        return $lifer->gameState()->lockForUpdate()->firstOrFail();
    }

    /** @param array<string, mixed> $context */
    private function audit(Request $request, string $action, Lifer $lifer, array $context): void
    {
        AdminAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'target_user_id' => $lifer->user_id,
            'action' => $action,
            'context' => [
                'lifer_id' => $lifer->id,
                'lifer_name' => "{$lifer->first_name} {$lifer->last_name}",
                ...$context,
            ],
        ]);
    }
}
