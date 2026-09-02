<?php

namespace Tests\Feature;

use App\Models\FamilyRequest;
use App\Models\LiferMarriage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class FamilyAuthorizationTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_active_lifer_can_open_family_page_without_exposing_user_identity(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(firstName: 'Camille', lastName: 'Démo');
        [, $other] = $this->createUserWithLifer(firstName: 'Noa', lastName: 'Rivière');

        $this->actingAs($user)
            ->get(route('family.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Family/Index')
                ->where('currentLifer.id', $lifer->id)
                ->where('currentLifer.name', 'Camille Démo')
                ->where('otherLifers.0.id', $other->id)
                ->where('otherLifers.0.name', 'Noa Rivière')
                ->missing('otherLifers.0.user'));
    }

    public function test_lifer_can_send_family_request_through_http(): void
    {
        [$requesterUser, $requester] = $this->createUserWithLifer();
        [, $recipient] = $this->createUserWithLifer();

        $this->actingAs($requesterUser)
            ->post(route('family.requests.store'), [
                'recipient_lifer_id' => $recipient->id,
                'type' => FamilyRequest::TYPE_MARRIAGE,
            ])
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseHas('family_requests', [
            'requester_lifer_id' => $requester->id,
            'recipient_lifer_id' => $recipient->id,
            'type' => FamilyRequest::TYPE_MARRIAGE,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);
    }

    public function test_only_recipient_can_respond_to_family_request(): void
    {
        [$requesterUser, $requester] = $this->createUserWithLifer();
        [, $recipient] = $this->createUserWithLifer();
        $familyRequest = FamilyRequest::create([
            'requester_lifer_id' => $requester->id,
            'recipient_lifer_id' => $recipient->id,
            'type' => FamilyRequest::TYPE_MARRIAGE,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);

        $this->actingAs($requesterUser)
            ->patch(route('family.requests.respond', $familyRequest), ['accepted' => true])
            ->assertForbidden();

        $this->assertDatabaseHas('family_requests', [
            'id' => $familyRequest->id,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);
    }

    public function test_dead_lifer_cannot_be_targeted(): void
    {
        [$requesterUser] = $this->createUserWithLifer();
        [, $recipient] = $this->createUserWithLifer();
        $recipient->update([
            'status' => 'dead',
            'died_at' => now(),
            'age_at_death' => 18,
            'death_cause' => 'Cause de test',
        ]);

        $this->actingAs($requesterUser)
            ->post(route('family.requests.store'), [
                'recipient_lifer_id' => $recipient->id,
                'type' => FamilyRequest::TYPE_MARRIAGE,
            ])
            ->assertSessionHasErrors('recipient_lifer_id');

        $this->assertDatabaseCount('family_requests', 0);
    }

    public function test_requester_can_cancel_a_pending_request(): void
    {
        [$requesterUser, $requester] = $this->createUserWithLifer();
        [, $recipient] = $this->createUserWithLifer();
        $familyRequest = FamilyRequest::create([
            'requester_lifer_id' => $requester->id,
            'recipient_lifer_id' => $recipient->id,
            'type' => FamilyRequest::TYPE_INTIMACY_PROTECTED,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);

        $this->actingAs($requesterUser)
            ->delete(route('family.requests.cancel', $familyRequest))
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseHas('family_requests', [
            'id' => $familyRequest->id,
            'status' => FamilyRequest::STATUS_CANCELLED,
        ]);
        $this->assertNotNull($familyRequest->fresh()->responded_at);
    }

    public function test_recipient_cannot_cancel_a_request_sent_by_another_lifer(): void
    {
        [, $requester] = $this->createUserWithLifer();
        [$recipientUser, $recipient] = $this->createUserWithLifer();
        $familyRequest = FamilyRequest::create([
            'requester_lifer_id' => $requester->id,
            'recipient_lifer_id' => $recipient->id,
            'type' => FamilyRequest::TYPE_INTIMACY_PROTECTED,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);

        $this->actingAs($recipientUser)
            ->delete(route('family.requests.cancel', $familyRequest))
            ->assertForbidden();

        $this->assertDatabaseHas('family_requests', [
            'id' => $familyRequest->id,
            'status' => FamilyRequest::STATUS_PENDING,
        ]);
    }

    public function test_married_lifer_can_divorce_without_changing_family_history(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [, $spouse] = $this->createUserWithLifer();
        $marriage = LiferMarriage::create([
            'first_lifer_id' => $lifer->id,
            'second_lifer_id' => $spouse->id,
            'lower_lifer_id' => min($lifer->id, $spouse->id),
            'higher_lifer_id' => max($lifer->id, $spouse->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
            'started_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->delete(route('family.marriage.divorce'))
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseHas('lifer_marriages', [
            'id' => $marriage->id,
            'status' => LiferMarriage::STATUS_DIVORCED,
            'end_reason' => 'divorce',
        ]);
        $this->assertNotNull($marriage->fresh()->ended_at);
        $this->assertNull($lifer->activeMarriage());
    }

    public function test_family_favorites_are_scoped_to_the_active_lifer(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(firstName: 'Camille');
        [, $favorite] = $this->createUserWithLifer(firstName: 'Lou');

        $this->actingAs($user)
            ->post(route('family.favorites.store', $favorite))
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseHas('lifer_family_favorites', [
            'owner_lifer_id' => $lifer->id,
            'favorite_lifer_id' => $favorite->id,
        ]);

        $this->actingAs($user)
            ->get(route('family.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('otherLifers.0.id', $favorite->id)
                ->where('otherLifers.0.is_favorite', true));

        $this->actingAs($user)
            ->delete(route('family.favorites.destroy', $favorite))
            ->assertRedirect(route('family.index'));

        $this->assertDatabaseMissing('lifer_family_favorites', [
            'owner_lifer_id' => $lifer->id,
            'favorite_lifer_id' => $favorite->id,
        ]);
    }

    public function test_lifer_cannot_add_itself_to_family_favorites(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();

        $this->actingAs($user)
            ->post(route('family.favorites.store', $lifer))
            ->assertNotFound();

        $this->assertDatabaseCount('lifer_family_favorites', 0);
    }
}
