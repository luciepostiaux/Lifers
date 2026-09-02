<?php

namespace Tests\Feature;

use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\Item;
use App\Models\Lifer;
use App\Models\LiferMarriage;
use App\Services\FamilyActionRandomizer;
use App\Services\FamilyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class FamilyServiceTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_marriage_requires_a_request_and_recipient_acceptance(): void
    {
        [, $first] = $this->createUserWithLifer();
        [, $second] = $this->createUserWithLifer();
        $service = app(FamilyService::class);

        $request = $service->request($first, $second, FamilyRequest::TYPE_MARRIAGE);

        $this->assertSame(FamilyRequest::STATUS_PENDING, $request->status);
        $this->assertDatabaseCount('lifer_marriages', 0);

        $service->respond($request, $second, true);

        $this->assertDatabaseHas('lifer_marriages', [
            'lower_lifer_id' => min($first->id, $second->id),
            'higher_lifer_id' => max($first->id, $second->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
        ]);
    }

    public function test_rejected_intimacy_changes_no_gauge_and_consumes_no_protection(): void
    {
        [, $first] = $this->createUserWithLifer();
        [, $second] = $this->createUserWithLifer();
        $this->giveProtections($first, 1);
        $request = app(FamilyService::class)->request($first, $second, FamilyRequest::TYPE_INTIMACY_PROTECTED);

        app(FamilyService::class)->respond($request, $second, false);

        $this->assertSame(100, $first->lifeGauge()->value('happiness'));
        $this->assertDatabaseHas('inventory_items', [
            'inventory_id' => $first->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseCount('lifer_intimacy_events', 0);
    }

    public function test_protected_action_consumes_item_updates_both_lifers_and_counts_for_both(): void
    {
        [, $first] = $this->createUserWithLifer(900, [
            'happiness' => 50,
            'entertainment' => 50,
            'physical_condition' => 50,
            'clean' => 50,
            'hunger' => 50,
            'thirst' => 50,
        ]);
        [, $second] = $this->createUserWithLifer(900, [
            'happiness' => 99,
            'entertainment' => 99,
            'physical_condition' => 99,
            'clean' => 3,
            'hunger' => 3,
            'thirst' => 3,
        ]);
        $this->giveProtections($first, 1);
        $service = app(FamilyService::class);
        $request = $service->request($first, $second, FamilyRequest::TYPE_INTIMACY_PROTECTED);

        $service->respond($request, $second, true);

        $firstGauges = $first->lifeGauge()->firstOrFail();
        $secondGauges = $second->lifeGauge()->firstOrFail();
        $this->assertSame(53, $firstGauges->happiness);
        $this->assertSame(45, $firstGauges->clean);
        $this->assertSame(100, $secondGauges->happiness);
        $this->assertSame(0, $secondGauges->clean);
        $this->assertDatabaseMissing('inventory_items', ['inventory_id' => $first->id]);
        $this->assertDatabaseHas('lifer_intimacy_events', [
            'first_lifer_id' => $first->id,
            'second_lifer_id' => $second->id,
            'type' => 'protected',
        ]);
    }

    public function test_each_participant_is_limited_to_ten_protected_actions_per_day(): void
    {
        [, $first] = $this->createUserWithLifer();
        [, $second] = $this->createUserWithLifer();
        $this->giveProtections($first, 11);
        $service = app(FamilyService::class);
        $requests = [];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $requests[] = $service->request($first, $second, FamilyRequest::TYPE_INTIMACY_PROTECTED);
        }

        $this->assertDatabaseCount('family_requests', 10);
        $this->assertDatabaseCount('lifer_intimacy_events', 0);

        foreach ($requests as $request) {
            $service->respond($request, $second, true);
        }

        $this->assertDatabaseCount('lifer_intimacy_events', 10);

        $this->expectException(ValidationException::class);
        $service->request($first, $second, FamilyRequest::TYPE_INTIMACY_PROTECTED);
    }

    public function test_family_probabilities_and_daily_limits_match_validated_rules(): void
    {
        $this->assertSame(10, FamilyService::DAILY_LIMIT);
        $this->assertSame(25, FamilyActionRandomizer::CONCEPTION_CHANCE_PERCENT);
    }

    public function test_each_participant_is_limited_to_ten_baby_attempts_per_day(): void
    {
        [, $mother] = $this->createUserWithLifer();
        $mother->update(['sex' => Lifer::SEX_FEMALE]);
        [, $father] = $this->createUserWithLifer();
        $father->update(['sex' => Lifer::SEX_MALE]);
        $this->app->instance(FamilyActionRandomizer::class, new FixedFamilyRandomizer(false, 1, []));
        $service = app(FamilyService::class);
        $requests = [];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $requests[] = $service->request($father->fresh(), $mother->fresh(), FamilyRequest::TYPE_BABY_ATTEMPT);
        }

        $this->assertDatabaseCount('family_requests', 10);
        $this->assertDatabaseCount('lifer_intimacy_events', 0);

        foreach ($requests as $request) {
            $service->respond($request, $mother->fresh(), true);
        }

        $this->assertDatabaseCount('lifer_intimacy_events', 10);

        $this->expectException(ValidationException::class);
        $service->request($father->fresh(), $mother->fresh(), FamilyRequest::TYPE_BABY_ATTEMPT);
    }

    public function test_successful_baby_attempt_creates_one_pregnancy_and_expected_triplets(): void
    {
        [, $mother] = $this->createUserWithLifer();
        $mother->update(['sex' => Lifer::SEX_FEMALE]);
        [, $father] = $this->createUserWithLifer();
        $father->update(['sex' => Lifer::SEX_MALE]);
        $this->app->instance(FamilyActionRandomizer::class, new FixedFamilyRandomizer(true, 3, [
            Lifer::SEX_FEMALE,
            Lifer::SEX_MALE,
            Lifer::SEX_FEMALE,
        ]));
        $service = app(FamilyService::class);
        $request = $service->request($father->fresh(), $mother->fresh(), FamilyRequest::TYPE_BABY_ATTEMPT);

        $service->respond($request, $mother->fresh(), true);

        $pregnancy = FamilyPregnancy::with('children.guardians')->firstOrFail();
        $this->assertSame(3, $pregnancy->children_count);
        $this->assertSame($mother->id, $pregnancy->mother_lifer_id);
        $this->assertSame($father->id, $pregnancy->father_lifer_id);
        $this->assertCount(3, $pregnancy->children);
        $this->assertSame(
            [Lifer::SEX_FEMALE, Lifer::SEX_MALE, Lifer::SEX_FEMALE],
            $pregnancy->children->pluck('sex')->all(),
        );
        $this->assertTrue($pregnancy->children->every(fn ($child) => $child->guardians->count() === 2));
    }

    public function test_previous_day_active_pregnancy_blocks_new_baby_attempts(): void
    {
        [, $mother] = $this->createUserWithLifer();
        $mother->update(['sex' => Lifer::SEX_FEMALE]);
        [, $father] = $this->createUserWithLifer();
        $father->update(['sex' => Lifer::SEX_MALE]);
        FamilyPregnancy::create([
            'mother_lifer_id' => $mother->id,
            'father_lifer_id' => $father->id,
            'children_count' => 1,
            'status' => FamilyPregnancy::STATUS_ACTIVE,
            'conceived_at' => now()->subDay(),
            'due_at' => now()->addDays(2),
        ]);

        $this->expectException(ValidationException::class);
        app(FamilyService::class)->request($mother->fresh(), $father->fresh(), FamilyRequest::TYPE_BABY_ATTEMPT);
    }

    private function giveProtections(Lifer $lifer, int $quantity): void
    {
        $item = Item::firstOrCreate(
            ['name' => Item::FAMILY_PROTECTION_NAME],
            [
                'price' => 1,
                'description' => 'Uniquement pour les tests.',
                'category' => Item::CATEGORY_FAMILY_PROTECTION,
            ],
        );
        $lifer->inventory->items()->syncWithoutDetaching([
            $item->id => ['quantity' => $quantity],
        ]);
    }
}

class FixedFamilyRandomizer extends FamilyActionRandomizer
{
    public function __construct(
        private readonly bool $conception,
        private readonly int $count,
        private array $sexes,
    ) {}

    public function conceptionSucceeds(): bool
    {
        return $this->conception;
    }

    public function childrenCount(): int
    {
        return $this->count;
    }

    public function childSex(): string
    {
        return array_shift($this->sexes) ?? Lifer::SEX_FEMALE;
    }
}
