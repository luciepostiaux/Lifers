<?php

namespace Database\Seeders;

use App\Models\Sickness;
use App\Models\SicknessEffect;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SicknessCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Sickness::query()
                ->whereIn('name', ['Maladie incurable', 'Maladie incurable — démo'])
                ->get()
                ->each(function (Sickness $sickness) {
                    DB::table('lifer_sicknesses')->where('sickness_id', $sickness->id)->delete();
                    $sickness->delete();
                });

            foreach ($this->catalog() as $data) {
                $effects = $data['effects'];
                $aliases = $data['aliases'] ?? [];
                unset($data['effects'], $data['aliases']);

                $sickness = Sickness::query()
                    ->where('slug', $data['slug'])
                    ->orWhere('name', $data['name'])
                    ->first();

                if ($sickness) {
                    $sickness->update($data);
                } else {
                    $sickness = Sickness::create($data);
                }

                SicknessEffect::query()
                    ->where('sickness_id', $sickness->id)
                    ->when($effects !== [], fn ($query) => $query->whereNotIn('gauge', array_keys($effects)))
                    ->delete();

                foreach ($effects as $gauge => $effect) {
                    SicknessEffect::query()->updateOrCreate(
                        ['sickness_id' => $sickness->id, 'gauge' => $gauge],
                        ['effect' => $effect],
                    );
                }

                foreach ($aliases as $alias) {
                    $this->mergeLegacySickness($alias, $sickness);
                }
            }
        });
    }

    private function catalog(): array
    {
        return [
            $this->sickness('gastro-enterite', 'Gastro-entérite', 'random', 1, [5, 7, 10, 15], [
                'clean' => -10,
                'thirst' => -15,
                'happiness' => -10,
            ], 45, false, true, 'once'),
            $this->sickness('rhume', 'Rhume', 'random', 2, [6, 8, 11, 16], [
                'health' => -8,
                'clean' => -5,
            ], 35, false, true, 'daily', aliases: ['Rhume passager — démo']),
            $this->sickness('migraine', 'Migraine', 'random', 2, [4, 6, 9, 14], [
                'happiness' => -10,
                'health' => -5,
            ], 30, false, true, 'daily'),
            $this->sickness('allergie-saisonniere', 'Allergie saisonnière', 'random', 1, [7, 9, 12, 17], [
                'physical_condition' => -10,
                'health' => -8,
            ], 40, false, true, 'once'),
            $this->sickness('intoxication-alimentaire', 'Intoxication alimentaire', 'random', 1, [5, 7, 10, 15], [
                'health' => -15,
                'clean' => -10,
            ], 75, true, true, 'once', dailyDecayMultiplier: 2),
            $this->sickness('diabete-type-2', 'Diabète de type 2', 'negligence', null, null, [
                'health' => -6,
                'physical_condition' => -5,
            ], 220, true, false, 'daily', triggerType: 'days_without_sport', triggerDays: 10),
            $this->sickness('obesite', 'Obésité', 'negligence', null, null, [
                'health' => -5,
                'physical_condition' => -8,
            ], 150, true, false, 'daily', triggerType: 'days_without_sport', triggerDays: 15),
            $this->sickness('caries-dentaires', 'Caries dentaires', 'negligence', null, null, [
                'clean' => -6,
                'health' => -4,
            ], 80, true, false, 'daily', triggerType: 'days_without_item', triggerDays: 6, triggerConfig: [
                'usage_tag' => 'oral_care',
            ]),
            $this->sickness('depression', 'Dépression', 'negligence', null, null, [
                'happiness' => -8,
                'physical_condition' => -3,
            ], 180, true, false, 'daily', triggerType: 'gauge_streak', triggerDays: 6, triggerConfig: [
                'match' => 'all',
                'gauges' => [['gauge' => 'happiness', 'operator' => '<=', 'threshold' => 15]],
            ]),
            $this->sickness('cancer', 'Cancer', 'severe', null, [0.01, 0.03, 0.08, 0.15], [
                'health' => -10,
                'physical_condition' => -8,
                'happiness' => -5,
            ], 1000, true, false, 'daily', fatalAfterDays: 14, triggerType: 'random', riskConfig: [
                'maximum_multiplier' => 5,
                'exposures' => [
                    ['usage_tag' => 'tobacco', 'window_days' => 30, 'uses_for_double_risk' => 10],
                    ['usage_tag' => 'alcohol', 'window_days' => 30, 'uses_for_double_risk' => 20],
                ],
            ]),
            $this->sickness('insuffisance-renale', 'Insuffisance rénale', 'severe', null, null, [
                'health' => -12,
                'physical_condition' => -8,
                'thirst' => -8,
            ], 700, true, false, 'daily', fatalAfterDays: 14, triggerType: 'gauge_streak', triggerDays: 5, triggerConfig: [
                'match' => 'all',
                'gauges' => [
                    ['gauge' => 'health', 'operator' => '<=', 'threshold' => 30],
                    ['gauge' => 'physical_condition', 'operator' => '<=', 'threshold' => 30],
                    ['gauge' => 'thirst', 'operator' => '<=', 'threshold' => 30],
                ],
            ]),
        ];
    }

    private function sickness(
        string $slug,
        string $name,
        string $type,
        ?int $durationDays,
        ?array $ageChances,
        array $effects,
        ?int $treatmentCost,
        bool $needsDoctor,
        bool $selfResolving,
        string $effectTiming,
        array $aliases = [],
        float $dailyDecayMultiplier = 1,
        ?int $fatalAfterDays = null,
        ?string $triggerType = 'random',
        ?int $triggerDays = null,
        ?array $triggerConfig = null,
        ?array $riskConfig = null,
    ): array {
        return [
            'slug' => $slug,
            'name' => $name,
            'description' => $this->description($slug),
            'duration_days' => $durationDays,
            'chance_by_age' => $ageChances ? [
                '18-34' => $ageChances[0],
                '35-59' => $ageChances[1],
                '60-79' => $ageChances[2],
                '80+' => $ageChances[3],
            ] : null,
            'type' => $type,
            'needs_doctor' => $needsDoctor,
            'self_resolving' => $selfResolving,
            'treatment_cost' => $treatmentCost,
            'effect_timing' => $effectTiming,
            'daily_decay_multiplier' => $dailyDecayMultiplier,
            'fatal_after_days' => $fatalAfterDays,
            'trigger_type' => $triggerType,
            'trigger_days' => $triggerDays,
            'trigger_config' => $triggerConfig,
            'risk_config' => $riskConfig,
            'effects' => $effects,
            'aliases' => $aliases,
        ];
    }

    private function description(string $slug): string
    {
        return match ($slug) {
            'gastro-enterite' => 'Trouble digestif bref qui affecte l’hydratation, l’hygiène et le moral.',
            'rhume' => 'Infection passagère qui diminue progressivement la santé et l’hygiène.',
            'migraine' => 'Douleur temporaire qui pèse sur la santé et le bonheur.',
            'allergie-saisonniere' => 'Réaction saisonnière qui fatigue le Lifer et réduit sa santé.',
            'intoxication-alimentaire' => 'Maladie aiguë qui accentue pendant une journée la baisse de toutes les jauges.',
            'diabete-type-2' => 'Maladie durable favorisée par une longue période sans activité sportive.',
            'obesite' => 'État durable lié à une inactivité prolongée, avec des effets physiques progressifs.',
            'caries-dentaires' => 'Affection favorisée par l’absence prolongée de soin dentaire.',
            'depression' => 'Maladie durable pouvant apparaître lorsque le bonheur reste critique.',
            'cancer' => 'Maladie grave et mortelle sans traitement dans les quatorze jours.',
            'insuffisance-renale' => 'Maladie grave liée à une dégradation prolongée de la santé, de l’hydratation et de la condition physique.',
        };
    }

    private function mergeLegacySickness(string $legacyName, Sickness $target): void
    {
        $legacy = Sickness::query()->where('name', $legacyName)->whereKeyNot($target->id)->first();
        if (! $legacy) {
            return;
        }

        DB::table('lifer_sicknesses')
            ->where('sickness_id', $legacy->id)
            ->get()
            ->each(function ($pivot) use ($target) {
                DB::table('lifer_sicknesses')->updateOrInsert(
                    ['lifer_id' => $pivot->lifer_id, 'sickness_id' => $target->id],
                    [
                        'contracted_at' => $pivot->contracted_at,
                        'expected_recovery_at' => $pivot->expected_recovery_at,
                        'last_effect_applied_on' => $pivot->last_effect_applied_on,
                        'fatal_at' => $pivot->fatal_at,
                        'created_at' => $pivot->created_at ?? now(),
                        'updated_at' => now(),
                    ],
                );
            });

        DB::table('lifer_sicknesses')->where('sickness_id', $legacy->id)->delete();
        $legacy->delete();
    }
}
