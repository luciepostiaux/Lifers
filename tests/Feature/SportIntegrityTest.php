<?php

namespace Tests\Feature;

use App\Models\LiferSubscription;
use App\Models\SportSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class SportIntegrityTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_gym_subscription_charges_selected_plan_and_only_one_is_active(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(500);
        $first = $this->createPlan('Standard', 'gym', 130, 3, 40);
        $second = $this->createPlan('Premium', 'gym', 100, 7, 60);

        $this->actingAs($user)->post(route('city.subscribeToGym'), ['sportSessionId' => $first->id]);
        $this->actingAs($user)->post(route('city.subscribeToGym'), ['sportSessionId' => $second->id]);

        $this->assertSame('270.00', $lifer->gameState->fresh()->money);
        $this->assertSame(1, LiferSubscription::where('lifer_id', $lifer->id)->where('status', 'active')->count());
        $this->assertSame(1, LiferSubscription::where('lifer_id', $lifer->id)->where('status', 'cancelled')->count());
        $this->assertDatabaseHas('lifer_subscriptions', [
            'lifer_id' => $lifer->id,
            'sport_session_id' => $second->id,
            'status' => 'active',
        ]);
    }

    public function test_gym_subscription_is_not_created_without_enough_money(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(50);
        $plan = $this->createPlan('Premium', 'gym', 100, 7, 20);

        $this->actingAs($user)
            ->from(route('city.sport'))
            ->post(route('city.subscribeToGym'), ['sportSessionId' => $plan->id])
            ->assertSessionHasErrors('sportSessionId');

        $this->assertSame('50.00', $lifer->gameState->fresh()->money);
        $this->assertDatabaseCount('lifer_subscriptions', 0);
    }

    public function test_cancellation_targets_the_active_lifers_subscription(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $plan = $this->createPlan('Standard', 'gym', 100, 3, 20);
        $subscription = LiferSubscription::create([
            'lifer_id' => $lifer->id,
            'sport_session_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(3),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->from(route('city.sport'))
            ->post(route('city.cancelGymSubscription'))
            ->assertRedirect(route('city.sport'));

        $this->assertSame('cancelled', $subscription->fresh()->status);
    }

    public function test_single_session_charges_and_caps_its_effect_at_one_hundred(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(100, ['physical_condition' => 90]);
        $session = $this->createPlan('Séance', 'single', 40, 1, 15);

        $this->actingAs($user)
            ->from(route('city.sport'))
            ->post(route('city.buySingleSportSession'), ['sessionId' => $session->id])
            ->assertRedirect(route('city.sport'));

        $this->assertSame('60.00', $lifer->gameState->fresh()->money);
        $this->assertSame(100, $lifer->lifeGauge->fresh()->physical_condition);
    }

    public function test_expired_subscription_renews_only_if_lifer_can_pay(): void
    {
        [, $lifer] = $this->createUserWithLifer(500, ['physical_condition' => 30]);
        $plan = $this->createPlan('Basic', 'gym', 200, 7, 60);
        $subscription = LiferSubscription::create([
            'lifer_id' => $lifer->id,
            'sport_session_id' => $plan->id,
            'starts_at' => now()->subDays(8),
            'ends_at' => now()->subDay(),
            'status' => 'active',
        ]);

        $this->artisan('update:life-gauges-from-subscriptions')->assertSuccessful();

        $this->assertTrue($subscription->fresh()->ends_at->isFuture());
        $this->assertSame('300.00', $lifer->gameState->fresh()->money);
        $this->assertSame(90, $lifer->lifeGauge->fresh()->physical_condition);
    }

    private function createPlan(string $name, string $type, int $price, int $duration, int $effect): SportSession
    {
        return SportSession::create([
            'name' => $name,
            'type' => $type,
            'price' => $price,
            'duration_days' => $duration,
            'physical_condition_effect' => $effect,
        ]);
    }
}
