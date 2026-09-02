<?php

namespace Tests\Feature;

use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\Item;
use App\Models\Lifer;
use App\Models\LiferItemUsage;
use App\Models\Sickness;
use App\Services\GameRandomizer;
use App\Services\LiferLifecycleService;
use App\Services\NaturalMortalityService;
use App\Services\SicknessProgressionService;
use App\Services\SicknessRiskCalculator;
use App\Services\SicknessTriggerService;
use Database\Seeders\SicknessCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class DailyHealthLifecycleTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_natural_mortality_curve_matches_the_validated_age_markers(): void
    {
        $service = app(NaturalMortalityService::class);

        $this->assertEqualsWithDelta(0, $service->baseDailyChance(69), 0.001);
        $this->assertEqualsWithDelta(2, $service->baseDailyChance(70), 0.001);
        $this->assertEqualsWithDelta(7, $service->baseDailyChance(80), 0.001);
        $this->assertEqualsWithDelta(14, $service->baseDailyChance(90), 0.001);
        $this->assertEqualsWithDelta(39.5, $service->baseDailyChance(100), 0.001);
        $this->assertEqualsWithDelta(100, $service->baseDailyChance(110), 0.001);
    }

    public function test_ten_green_days_halve_the_natural_mortality_chance(): void
    {
        [, $lifer] = $this->createUserWithLifer(900, [
            'hunger' => 61,
            'thirst' => 61,
            'health' => 61,
        ]);
        $lifer->update(['born_at' => now()->subDays((90 - 18) * 3)]);
        $lifer->gameState()->update(['vital_green_streak_days' => 9]);

        $randomizer = new RecordingGameRandomizer(false);
        $this->app->instance(GameRandomizer::class, $randomizer);

        app(NaturalMortalityService::class)->processAll();

        $this->assertEqualsWithDelta(7, $randomizer->lastPercentage, 0.001);
        $this->assertSame(10, $lifer->gameState->fresh()->vital_green_streak_days);
        $this->assertSame(Lifer::STATUS_ACTIVE, $lifer->fresh()->status);
    }

    public function test_a_lifer_dies_after_seven_full_days_with_all_vital_gauges_red(): void
    {
        [, $lifer] = $this->createUserWithLifer(900, [
            'hunger' => 15,
            'thirst' => 15,
            'health' => 15,
        ]);
        $lifer->gameState()->update(['vital_red_since' => today()->subDays(7)]);

        $this->app->instance(GameRandomizer::class, new RecordingGameRandomizer(false));
        $result = app(NaturalMortalityService::class)->processAll();

        $this->assertSame(1, $result['neglect_deaths']);
        $this->assertSame(Lifer::STATUS_DEAD, $lifer->fresh()->status);
        $this->assertSame('Négligence des besoins vitaux', $lifer->fresh()->death_cause);
        $this->assertDatabaseMissing('lifer_game_states', ['lifer_id' => $lifer->id]);
    }

    public function test_a_lifer_dies_automatically_at_one_hundred_and_ten(): void
    {
        [, $lifer] = $this->createUserWithLifer();
        $lifer->update(['born_at' => now()->subDays((110 - 18) * 3)]);

        app(NaturalMortalityService::class)->processAll();

        $this->assertSame(Lifer::STATUS_DEAD, $lifer->fresh()->status);
        $this->assertSame('Mort naturelle liée à l’âge', $lifer->fresh()->death_cause);
        $this->assertSame(110, $lifer->fresh()->age_at_death);
    }

    public function test_fatal_sickness_kills_when_its_treatment_deadline_has_passed(): void
    {
        $this->seed(SicknessCatalogSeeder::class);
        [, $lifer] = $this->createUserWithLifer();
        $cancer = Sickness::where('slug', 'cancer')->firstOrFail();
        $lifer->sicknesses()->attach($cancer->id, [
            'contracted_at' => now()->subDays(15),
            'fatal_at' => now()->subDay(),
            'last_effect_applied_on' => today()->subDay(),
        ]);

        $result = app(SicknessProgressionService::class)->processAll();

        $this->assertSame(1, $result['deaths']);
        $this->assertSame(Lifer::STATUS_DEAD, $lifer->fresh()->status);
        $this->assertSame('Cancer non traité', $lifer->fresh()->death_cause);
    }

    public function test_death_cancels_pending_requests_and_a_carrier_pregnancy(): void
    {
        [, $carrier] = $this->createUserWithLifer();
        [, $other] = $this->createUserWithLifer();
        $request = FamilyRequest::create([
            'requester_lifer_id' => $carrier->id,
            'recipient_lifer_id' => $other->id,
            'type' => FamilyRequest::TYPE_BABY_ATTEMPT,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);
        $pregnancy = FamilyPregnancy::create([
            'mother_lifer_id' => $carrier->id,
            'father_lifer_id' => $other->id,
            'children_count' => 1,
            'status' => FamilyPregnancy::STATUS_ACTIVE,
            'conceived_at' => now(),
            'due_at' => now()->addDays(2),
        ]);

        app(LiferLifecycleService::class)->die($carrier, 'Cause de test');

        $this->assertDatabaseMissing('family_requests', ['id' => $request->id]);
        $this->assertSame(FamilyPregnancy::STATUS_LOST, $pregnancy->fresh()->status);
        $this->assertNotNull($pregnancy->fresh()->completed_at);
    }

    public function test_catalog_contains_the_supported_diseases_without_incurable_disease(): void
    {
        $this->seed(SicknessCatalogSeeder::class);

        $this->assertSame(11, Sickness::count());
        $this->assertDatabaseHas('sicknesses', [
            'slug' => 'cancer',
            'fatal_after_days' => 14,
        ]);
        $this->assertDatabaseHas('sicknesses', [
            'slug' => 'insuffisance-renale',
            'fatal_after_days' => 14,
        ]);
        $this->assertDatabaseMissing('sicknesses', ['name' => 'Maladie incurable']);
    }

    public function test_prolonged_neglect_triggers_inactivity_and_dental_sicknesses(): void
    {
        $this->seed(SicknessCatalogSeeder::class);
        [, $lifer] = $this->createUserWithLifer();
        $lifer->forceFill(['created_at' => now()->subDays(16)])->save();

        $contracted = app(SicknessTriggerService::class)->processAll();

        $this->assertSame(3, $contracted);
        $this->assertTrue($lifer->sicknesses()->where('slug', 'diabete-type-2')->exists());
        $this->assertTrue($lifer->sicknesses()->where('slug', 'obesite')->exists());
        $this->assertTrue($lifer->sicknesses()->where('slug', 'caries-dentaires')->exists());
    }

    public function test_food_poisoning_doubles_the_daily_gauge_decay(): void
    {
        $this->seed(SicknessCatalogSeeder::class);
        [, $lifer] = $this->createUserWithLifer(900, [
            'hunger' => 100,
            'thirst' => 100,
            'clean' => 100,
            'happiness' => 100,
            'entertainment' => 100,
            'physical_condition' => 100,
            'health' => 100,
        ]);
        $sickness = Sickness::where('slug', 'intoxication-alimentaire')->firstOrFail();
        $lifer->sicknesses()->attach($sickness->id, [
            'contracted_at' => now(),
            'expected_recovery_at' => now()->addDay(),
        ]);

        $this->artisan('decrease:life-gauges')->assertSuccessful();

        $gauges = $lifer->lifeGauge->fresh();
        $this->assertSame(40, $gauges->hunger);
        $this->assertSame(30, $gauges->thirst);
        $this->assertSame(80, $gauges->health);
    }

    public function test_daily_tick_is_idempotent_for_gauges_and_health_checks(): void
    {
        [, $lifer] = $this->createUserWithLifer(900, [
            'hunger' => 100,
            'thirst' => 100,
            'health' => 100,
        ]);
        $this->app->instance(GameRandomizer::class, new RecordingGameRandomizer(false));

        $this->artisan('lifers:daily-tick')->assertSuccessful();
        $this->artisan('lifers:daily-tick')->assertSuccessful();

        $this->assertSame(70, $lifer->lifeGauge->fresh()->hunger);
        $this->assertSame(65, $lifer->lifeGauge->fresh()->thirst);
        $this->assertSame(90, $lifer->lifeGauge->fresh()->health);
        $this->assertTrue($lifer->gameState->fresh()->last_mortality_checked_on->isToday());
        $this->assertTrue($lifer->gameState->fresh()->last_sickness_trigger_checked_on->isToday());
    }

    public function test_regular_tobacco_and_alcohol_use_increases_cancer_risk(): void
    {
        $this->seed(SicknessCatalogSeeder::class);
        [, $lifer] = $this->createUserWithLifer();
        $cancer = Sickness::where('slug', 'cancer')->firstOrFail();
        $tobacco = Item::factory()->create(['usage_tag' => 'tobacco']);
        $alcohol = Item::factory()->create(['usage_tag' => 'alcohol']);
        $calculator = app(SicknessRiskCalculator::class);

        $baseline = $calculator->dailyChance($lifer, $cancer);

        LiferItemUsage::create([
            'lifer_id' => $lifer->id,
            'item_id' => $tobacco->id,
            'usage_tag' => 'tobacco',
            'quantity' => 10,
            'used_at' => now(),
        ]);
        LiferItemUsage::create([
            'lifer_id' => $lifer->id,
            'item_id' => $alcohol->id,
            'usage_tag' => 'alcohol',
            'quantity' => 20,
            'used_at' => now(),
        ]);

        $this->assertEqualsWithDelta($baseline * 3, $calculator->dailyChance($lifer, $cancer), 0.0001);
    }
}

class RecordingGameRandomizer extends GameRandomizer
{
    public ?float $lastPercentage = null;

    public function __construct(private readonly bool $result) {}

    public function succeeds(float $percentage): bool
    {
        $this->lastPercentage = $percentage;

        return $this->result;
    }
}
