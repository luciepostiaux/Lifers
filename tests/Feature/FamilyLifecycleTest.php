<?php

namespace Tests\Feature;

use App\Models\FamilyChild;
use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\GivenName;
use App\Models\Lifer;
use App\Models\LiferMarriage;
use App\Services\FamilyLifecycleService;
use App\Services\FamilyService;
use App\Services\LiferLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class FamilyLifecycleTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_parent_with_custody_can_prepare_child_name_before_birth(): void
    {
        [$motherUser, $mother] = $this->createUserWithLifer(firstName: 'Ava', lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(firstName: 'Noé', lastName: 'Martin');
        [$pregnancy, $child] = $this->expectedChild($mother, $father);

        $this->actingAs($motherUser)
            ->patch(route('family.children.name', [$pregnancy, $child]), [
                'first_name' => 'Lou',
                'last_name' => 'Martin',
            ])
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseHas('family_children', [
            'id' => $child->id,
            'first_name' => 'Lou',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_EXPECTED,
        ]);
    }

    public function test_name_must_use_one_of_the_two_parent_last_names(): void
    {
        [$motherUser, $mother] = $this->createUserWithLifer(lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(lastName: 'Martin');
        [$pregnancy, $child] = $this->expectedChild($mother, $father);

        $this->actingAs($motherUser)
            ->patch(route('family.children.name', [$pregnancy, $child]), [
                'first_name' => 'Lou',
                'last_name' => 'Durand',
            ])
            ->assertSessionHasErrors('last_name');

        $this->assertNull($child->fresh()->first_name);
    }

    public function test_lifer_without_custody_cannot_name_expected_child(): void
    {
        [, $mother] = $this->createUserWithLifer(lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(lastName: 'Martin');
        [$outsiderUser] = $this->createUserWithLifer();
        [$pregnancy, $child] = $this->expectedChild($mother, $father);

        $this->actingAs($outsiderUser)
            ->patch(route('family.children.name', [$pregnancy, $child]), [
                'first_name' => 'Lou',
                'last_name' => 'Martin',
            ])
            ->assertForbidden();

        $this->assertNull($child->fresh()->first_name);
    }

    public function test_due_birth_uses_random_first_name_and_mothers_name_when_parents_are_not_married(): void
    {
        [, $mother] = $this->createUserWithLifer(lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(lastName: 'Martin');
        [$pregnancy, $child] = $this->expectedChild($mother, $father);
        $pregnancy->update([
            'conceived_at' => now()->subDays(2),
            'due_at' => now()->subSecond(),
        ]);
        $child->update(['conceived_at' => now()->subDays(2)]);
        GivenName::create(['name' => 'Lou', 'sex' => Lifer::SEX_FEMALE]);

        $births = app(FamilyLifecycleService::class)->birthDuePregnancies();

        $this->assertSame(1, $births);
        $this->assertDatabaseHas('family_children', [
            'id' => $child->id,
            'first_name' => 'Lou',
            'last_name' => 'Rivière',
            'status' => FamilyChild::STATUS_DEPENDENT,
        ]);
        $this->assertDatabaseHas('family_child_gauges', [
            'child_id' => $child->id,
            'hunger' => 100,
            'hygiene' => 100,
            'affection' => 100,
        ]);
    }

    public function test_due_birth_uses_fathers_name_when_biological_parents_are_married(): void
    {
        [, $mother] = $this->createUserWithLifer(lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(lastName: 'Martin');
        [$pregnancy, $child] = $this->expectedChild($mother, $father);
        $pregnancy->update([
            'conceived_at' => now()->subDays(2),
            'due_at' => now()->subSecond(),
        ]);
        $child->update(['conceived_at' => now()->subDays(3), 'first_name' => 'Alice']);
        LiferMarriage::create([
            'first_lifer_id' => $mother->id,
            'second_lifer_id' => $father->id,
            'lower_lifer_id' => min($mother->id, $father->id),
            'higher_lifer_id' => max($mother->id, $father->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
            'started_at' => now()->subDay(),
        ]);

        app(FamilyLifecycleService::class)->birthDuePregnancies();

        $this->assertDatabaseHas('family_children', [
            'id' => $child->id,
            'first_name' => 'Alice',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_DEPENDENT,
        ]);
    }

    public function test_child_leaves_custody_and_loses_temporary_gauges_at_eighteen(): void
    {
        [, $mother] = $this->createUserWithLifer();
        [, $father] = $this->createUserWithLifer();
        [$pregnancy, $child] = $this->expectedChild($mother, $father);
        $child->update([
            'first_name' => 'Lou',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_DEPENDENT,
            'born_at' => now()->subDays(54),
            'adult_at' => now()->subSecond(),
        ]);
        $child->gauges()->create();

        $released = app(FamilyLifecycleService::class)->releaseAdultChildren();

        $this->assertSame(1, $released);
        $this->assertSame(FamilyChild::STATUS_AVAILABLE, $child->fresh()->status);
        $this->assertDatabaseMissing('family_child_gauges', ['child_id' => $child->id]);
        $this->assertDatabaseMissing('family_child_guardians', [
            'child_id' => $child->id,
            'has_custody' => true,
        ]);
    }

    public function test_parent_can_feed_wash_and_cuddle_child_with_validated_costs(): void
    {
        [$user, $parent] = $this->createUserWithLifer(30);
        [, $otherParent] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($parent, $otherParent, [
            'hunger' => 20,
            'hygiene' => 30,
            'affection' => 40,
        ]);
        $service = app(FamilyLifecycleService::class);

        $service->careForChild($parent, $child, FamilyLifecycleService::CARE_FEED);
        $service->careForChild($parent, $child, FamilyLifecycleService::CARE_WASH);
        $service->careForChild($parent, $child, FamilyLifecycleService::CARE_CUDDLE);

        $this->assertSame('20.00', $parent->gameState->fresh()->money);
        $this->assertDatabaseHas('family_child_gauges', [
            'child_id' => $child->id,
            'hunger' => 40,
            'hygiene' => 50,
            'affection' => 60,
            'red_since' => null,
        ]);
        $this->actingAs($user)->get(route('family.index'))->assertOk();
    }

    public function test_caring_for_all_children_only_charges_for_children_who_need_care(): void
    {
        [, $parent] = $this->createUserWithLifer(10);
        [, $otherParent] = $this->createUserWithLifer();
        [, $firstChild] = $this->dependentChild($parent, $otherParent, ['hunger' => 10]);
        [, $secondChild] = $this->dependentChild($parent, $otherParent);

        $childrenCount = app(FamilyLifecycleService::class)->careForAllChildren($parent);

        $this->assertSame(1, $childrenCount);
        $this->assertSame('0.00', $parent->gameState->fresh()->money);
        $this->assertDatabaseHas('family_child_gauges', [
            'child_id' => $firstChild->id,
            'hunger' => 30,
            'hygiene' => 100,
            'affection' => 100,
        ]);
        $this->assertDatabaseHas('family_child_gauges', [
            'child_id' => $secondChild->id,
            'hunger' => 100,
            'hygiene' => 100,
            'affection' => 100,
        ]);
    }

    public function test_caring_for_all_children_charges_nothing_when_every_need_is_full(): void
    {
        [, $parent] = $this->createUserWithLifer(20);
        [, $otherParent] = $this->createUserWithLifer();
        $this->dependentChild($parent, $otherParent);

        try {
            app(FamilyLifecycleService::class)->careForAllChildren($parent);
            $this->fail('Le soin global aurait dû être refusé lorsque toutes les jauges sont pleines.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('care', $exception->errors());
        }

        $this->assertSame('20.00', $parent->gameState->fresh()->money);
    }

    public function test_child_gauges_decrease_only_once_per_day(): void
    {
        [, $parent] = $this->createUserWithLifer();
        [, $otherParent] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($parent, $otherParent);

        $first = app(FamilyLifecycleService::class)->decreaseChildGauges();
        $second = app(FamilyLifecycleService::class)->decreaseChildGauges();

        $this->assertSame(['updated' => 1, 'deaths' => 0], $first);
        $this->assertSame(['updated' => 0, 'deaths' => 0], $second);
        $this->assertDatabaseHas('family_child_gauges', [
            'child_id' => $child->id,
            'hunger' => 90,
            'hygiene' => 90,
            'affection' => 90,
        ]);
    }

    public function test_child_dies_after_remaining_in_red_for_three_real_days(): void
    {
        [, $parent] = $this->createUserWithLifer();
        [, $otherParent] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($parent, $otherParent, [
            'hunger' => 15,
            'hygiene' => 15,
            'affection' => 15,
            'red_since' => today()->subDays(3),
            'last_decreased_on' => today()->subDay(),
        ]);

        $result = app(FamilyLifecycleService::class)->decreaseChildGauges();

        $this->assertSame(['updated' => 0, 'deaths' => 1], $result);
        $this->assertSame(FamilyChild::STATUS_DEAD, $child->fresh()->status);
        $this->assertDatabaseMissing('family_child_gauges', ['child_id' => $child->id]);
    }

    public function test_renouncing_is_free_immediate_and_leaves_custody_to_other_parent(): void
    {
        [, $firstParent] = $this->createUserWithLifer(100);
        [, $secondParent] = $this->createUserWithLifer(100);
        [, $child] = $this->dependentChild($firstParent, $secondParent);

        app(FamilyLifecycleService::class)->renounceChild($firstParent, $child);

        $this->assertSame('100.00', $firstParent->gameState->fresh()->money);
        $this->assertDatabaseHas('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $firstParent->id,
            'has_custody' => false,
        ]);
        $this->assertDatabaseHas('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $secondParent->id,
            'has_custody' => true,
        ]);
    }

    public function test_two_parent_abandonment_requires_acceptance_and_charges_fifty_each(): void
    {
        [, $firstParent] = $this->createUserWithLifer(100);
        [, $secondParent] = $this->createUserWithLifer(100);
        [, $child] = $this->dependentChild($firstParent, $secondParent);
        $lifecycle = app(FamilyLifecycleService::class);

        $request = $lifecycle->requestOrAbandonChild($firstParent, $child);

        $this->assertInstanceOf(FamilyRequest::class, $request);
        $this->assertSame(FamilyChild::STATUS_DEPENDENT, $child->fresh()->status);

        app(FamilyService::class)->respond($request, $secondParent, true);

        $this->assertSame('50.00', $firstParent->gameState->fresh()->money);
        $this->assertSame('50.00', $secondParent->gameState->fresh()->money);
        $this->assertSame(FamilyChild::STATUS_ORPHANED, $child->fresh()->status);
        $this->assertDatabaseMissing('family_child_guardians', [
            'child_id' => $child->id,
            'has_custody' => true,
        ]);
    }

    public function test_single_parent_abandonment_is_immediate_and_costs_one_hundred(): void
    {
        [, $firstParent] = $this->createUserWithLifer(100);
        [, $secondParent] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($firstParent, $secondParent);
        app(FamilyLifecycleService::class)->renounceChild($secondParent, $child);

        $request = app(FamilyLifecycleService::class)->requestOrAbandonChild($firstParent, $child);

        $this->assertNull($request);
        $this->assertSame('0.00', $firstParent->gameState->fresh()->money);
        $this->assertSame(FamilyChild::STATUS_ORPHANED, $child->fresh()->status);
    }

    public function test_married_adopter_adds_spouse_while_single_adopter_stays_alone(): void
    {
        [, $biologicalParent] = $this->createUserWithLifer();
        [, $otherBiologicalParent] = $this->createUserWithLifer();
        [, $singleAdopter] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($biologicalParent, $otherBiologicalParent);
        $child->guardians()->updateExistingPivot($biologicalParent->id, ['has_custody' => false]);
        $child->guardians()->updateExistingPivot($otherBiologicalParent->id, ['has_custody' => false]);
        $child->update(['status' => FamilyChild::STATUS_ORPHANED]);

        $singleAdopters = app(FamilyLifecycleService::class)->adoptChild($singleAdopter, $child);

        $this->assertCount(1, $singleAdopters);
        $this->assertDatabaseHas('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $singleAdopter->id,
            'has_custody' => true,
        ]);

        [, $secondChild] = $this->dependentChild($biologicalParent, $otherBiologicalParent);
        DB::table('family_child_guardians')->where('child_id', $secondChild->id)->update(['has_custody' => false]);
        $secondChild->update(['status' => FamilyChild::STATUS_ORPHANED]);
        [, $marriedAdopter] = $this->createUserWithLifer();
        [, $spouse] = $this->createUserWithLifer();
        $this->marry($marriedAdopter, $spouse);

        $couple = app(FamilyLifecycleService::class)->adoptChild($marriedAdopter, $secondChild);

        $this->assertCount(2, $couple);
        foreach ([$marriedAdopter, $spouse] as $adopter) {
            $this->assertDatabaseHas('family_child_guardians', [
                'child_id' => $secondChild->id,
                'lifer_id' => $adopter->id,
                'type' => 'adoptive',
                'has_custody' => true,
            ]);
        }
    }

    public function test_marriage_allows_spouse_to_share_childcare_without_changing_lineage(): void
    {
        [, $parent] = $this->createUserWithLifer();
        [, $otherParent] = $this->createUserWithLifer();
        [, $spouse] = $this->createUserWithLifer(100);
        [, $child] = $this->dependentChild($parent, $otherParent, ['affection' => 10]);
        $this->marry($parent, $spouse);

        app(FamilyLifecycleService::class)->careForChild($spouse, $child, FamilyLifecycleService::CARE_CUDDLE);

        $this->assertSame(30, $child->gauges->fresh()->affection);
        $this->assertDatabaseMissing('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $spouse->id,
        ]);
    }

    public function test_child_goes_to_orphanage_only_when_no_active_custodian_remains(): void
    {
        [, $firstParent] = $this->createUserWithLifer();
        [, $secondParent] = $this->createUserWithLifer();
        [, $child] = $this->dependentChild($firstParent, $secondParent);

        app(LiferLifecycleService::class)->die($firstParent, 'Cause de test');
        $this->assertSame(FamilyChild::STATUS_DEPENDENT, $child->fresh()->status);

        app(LiferLifecycleService::class)->die($secondParent, 'Cause de test');
        $this->assertSame(FamilyChild::STATUS_ORPHANED, $child->fresh()->status);
    }

    private function expectedChild(Lifer $mother, Lifer $father): array
    {
        $mother->update(['sex' => Lifer::SEX_FEMALE]);
        $father->update(['sex' => Lifer::SEX_MALE]);
        $pregnancy = FamilyPregnancy::create([
            'mother_lifer_id' => $mother->id,
            'father_lifer_id' => $father->id,
            'children_count' => 1,
            'status' => FamilyPregnancy::STATUS_ACTIVE,
            'conceived_at' => now(),
            'due_at' => now()->addDays(2),
        ]);
        $child = FamilyChild::create([
            'pregnancy_id' => $pregnancy->id,
            'biological_mother_lifer_id' => $mother->id,
            'biological_father_lifer_id' => $father->id,
            'birth_order' => 1,
            'sex' => Lifer::SEX_FEMALE,
            'status' => FamilyChild::STATUS_EXPECTED,
            'conceived_at' => now(),
        ]);
        $child->guardians()->attach([
            $mother->id => ['type' => 'biological', 'has_custody' => true],
            $father->id => ['type' => 'biological', 'has_custody' => true],
        ]);

        return [$pregnancy, $child];
    }

    private function dependentChild(Lifer $firstParent, Lifer $secondParent, array $gauges = []): array
    {
        [$pregnancy, $child] = $this->expectedChild($firstParent, $secondParent);
        $child->update([
            'first_name' => 'Enfant',
            'last_name' => $firstParent->last_name,
            'status' => FamilyChild::STATUS_DEPENDENT,
            'born_at' => now(),
            'adult_at' => now()->addDays(54),
        ]);
        $child->gauges()->create([
            'hunger' => 100,
            'hygiene' => 100,
            'affection' => 100,
            ...$gauges,
        ]);

        return [$pregnancy, $child->fresh('gauges')];
    }

    private function marry(Lifer $first, Lifer $second): LiferMarriage
    {
        return LiferMarriage::create([
            'first_lifer_id' => $first->id,
            'second_lifer_id' => $second->id,
            'lower_lifer_id' => min($first->id, $second->id),
            'higher_lifer_id' => max($first->id, $second->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }
}
