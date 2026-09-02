<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityEffect;
use App\Models\Item;
use App\Models\ItemEffect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class TransactionIntegrityTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_consuming_an_item_applies_effect_and_removes_one_unit(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(500, ['happiness' => 50]);
        $item = $this->createItemWithEffect('happiness', 8);
        $this->attachItem($lifer->id, $item->id, 1);

        $this->actingAs($user)
            ->from(route('athome'))
            ->post(route('consume-item'), ['itemId' => $item->id])
            ->assertRedirect(route('athome'));

        $this->assertSame(58, $lifer->lifeGauge->fresh()->happiness);
        $this->assertDatabaseMissing('inventory_items', ['inventory_id' => $lifer->id, 'item_id' => $item->id]);
    }

    public function test_consuming_an_unowned_item_changes_nothing(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(500, ['happiness' => 50]);
        $item = $this->createItemWithEffect('happiness', 8);

        $this->actingAs($user)
            ->from(route('athome'))
            ->post(route('consume-item'), ['itemId' => $item->id])
            ->assertSessionHasErrors('itemId');

        $this->assertSame(50, $lifer->lifeGauge->fresh()->happiness);
    }

    public function test_purchase_charges_server_price_and_accumulates_quantity(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(100);
        $item = Item::factory()->create(['price' => 15]);

        foreach ([2, 1] as $quantity) {
            $this->actingAs($user)->post(route('purchase'), [
                'productId' => $item->id,
                'quantity' => $quantity,
                'price' => 0,
            ]);
        }

        $this->assertSame('55.00', $lifer->gameState->fresh()->money);
        $this->assertDatabaseHas('inventory_items', [
            'inventory_id' => $lifer->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_packaged_item_adds_all_units_while_charging_per_box(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(100);
        $item = Item::factory()->create([
            'price' => 10,
            'units_per_purchase' => 20,
        ]);

        $this->actingAs($user)->post(route('purchase'), [
            'productId' => $item->id,
            'quantity' => 2,
        ]);

        $this->assertSame('80.00', $lifer->gameState->fresh()->money);
        $this->assertDatabaseHas('inventory_items', [
            'inventory_id' => $lifer->id,
            'item_id' => $item->id,
            'quantity' => 40,
        ]);
    }

    public function test_activity_payment_and_effect_are_applied_together(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(100, ['happiness' => 50]);
        $activity = Activity::create([
            'name' => 'Cinéma',
            'description' => 'Activité de test',
            'price' => 40,
            'category' => 'Loisir',
        ]);
        ActivityEffect::create(['activity_id' => $activity->id, 'gauge' => 'happiness', 'effect' => 20]);

        $this->actingAs($user)
            ->from(route('city.entertainment'))
            ->post(route('city.participate'), ['activityId' => $activity->id])
            ->assertRedirect(route('city.entertainment'));

        $this->assertSame('60.00', $lifer->gameState->fresh()->money);
        $this->assertSame(70, $lifer->lifeGauge->fresh()->happiness);
    }

    public function test_doctor_visit_is_atomic_for_insufficient_and_sufficient_money(): void
    {
        [$poorUser, $poorLifer] = $this->createUserWithLifer(100, ['health' => 30]);
        $this->actingAs($poorUser)
            ->from(route('doctor.index'))
            ->post(route('visit-doctor'))
            ->assertSessionHasErrors('doctor');
        $this->assertSame('100.00', $poorLifer->gameState->fresh()->money);
        $this->assertSame(30, $poorLifer->lifeGauge->fresh()->health);

        $this->flushSession();
        [$user, $lifer] = $this->createUserWithLifer(200, ['health' => 30]);
        $this->actingAs($user)
            ->from(route('doctor.index'))
            ->post(route('visit-doctor'))
            ->assertRedirect(route('doctor.index'));
        $this->assertSame('50.00', $lifer->gameState->fresh()->money);
        $this->assertSame(100, $lifer->lifeGauge->fresh()->health);
    }

    private function createItemWithEffect(string $gauge, int $effect): Item
    {
        $item = Item::factory()->create();
        ItemEffect::create(['item_id' => $item->id, 'gauge' => $gauge, 'effect' => $effect]);

        return $item;
    }

    private function attachItem(int $inventoryId, int $itemId, int $quantity): void
    {
        DB::table('inventory_items')->insert([
            'inventory_id' => $inventoryId,
            'item_id' => $itemId,
            'quantity' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
