<?php

namespace Tests\Feature;

use App\Models\BodyType;
use App\Models\Conversation;
use App\Models\Item;
use App\Models\Lifer;
use App\Models\Message;
use App\Models\Sickness;
use App\Models\User;
use App\Services\LiferLifecycleService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class LiferLifecycleTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_age_starts_at_eighteen_and_advances_every_three_real_days(): void
    {
        $lifer = new Lifer(['born_at' => now()->subDays(7), 'status' => Lifer::STATUS_ACTIVE]);

        $this->assertSame(20, $lifer->calculateAge());
    }

    public function test_body_a_and_body_b_map_to_male_and_female_without_changing_ui_codes(): void
    {
        $this->seed();

        $this->assertDatabaseHas('body_types', ['code' => 'A', 'label' => 'Corps A', 'sex' => 'male']);
        $this->assertDatabaseHas('body_types', ['code' => 'B', 'label' => 'Corps B', 'sex' => 'female']);
    }

    public function test_an_account_cannot_have_two_active_lifers(): void
    {
        [$user] = $this->createUserWithLifer();

        $this->expectException(QueryException::class);

        Lifer::factory()->for($user)->create();
    }

    public function test_death_freezes_identity_deletes_game_state_and_allows_a_new_lifer(): void
    {
        $this->seed();
        [$user, $lifer] = $this->createUserWithLifer();
        $item = Item::factory()->create();
        DB::table('inventory_items')->insert([
            'inventory_id' => $lifer->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conversation = Conversation::where('key', 'general')->firstOrFail();
        $conversation->lifers()->attach($lifer->id);
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $lifer->id,
            'content' => 'Souvenir conservé',
        ]);

        $deadLifer = app(LiferLifecycleService::class)->die($lifer, 'Vieillesse');

        $this->assertSame(Lifer::STATUS_DEAD, $deadLifer->status);
        $this->assertSame('Vieillesse', $deadLifer->death_cause);
        $this->assertNotNull($deadLifer->age_at_death);
        $this->assertDatabaseMissing('lifer_game_states', ['lifer_id' => $lifer->id]);
        $this->assertDatabaseMissing('inventory_items', ['inventory_id' => $lifer->id]);
        $this->assertDatabaseHas('messages', ['id' => $message->id, 'sender_lifer_id' => $lifer->id]);

        $newLifer = app(LiferLifecycleService::class)->create(
            $user,
            BodyType::where('code', 'B')->firstOrFail(),
            ['first_name' => 'Nouvelle', 'last_name' => 'Vie'],
        );

        $this->assertSame('female', $newLifer->sex);
        $this->assertSame($newLifer->id, $user->activeLifer()->value('id'));
        $this->assertCount(2, $user->lifers()->get());

        $this->actingAs($user)->get('/social')->assertRedirect('/social/'.$conversation->id);
        $this->get("/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_deleting_account_removes_dead_identity_and_preserved_messages(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $conversation = Conversation::create([
            'name' => 'Général',
            'type' => Conversation::TYPE_GENERAL,
            'key' => 'general',
        ]);
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $lifer->id,
            'content' => 'Effacé avec le compte',
        ]);
        app(LiferLifecycleService::class)->die($lifer, 'Accident');

        $user->delete();

        $this->assertDatabaseMissing('lifers', ['id' => $lifer->id]);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_a_lifer_can_have_several_distinct_sicknesses_but_not_duplicates(): void
    {
        [, $lifer] = $this->createUserWithLifer();
        $first = Sickness::create([
            'name' => 'Rhume',
            'type' => 'random',
            'self_resolving' => true,
        ]);
        $second = Sickness::create([
            'name' => 'Grippe',
            'type' => 'random',
            'self_resolving' => true,
        ]);

        $lifer->sicknesses()->attach([
            $first->id => ['contracted_at' => now()],
            $second->id => ['contracted_at' => now()],
        ]);

        $this->assertCount(2, $lifer->sicknesses()->get());

        $this->expectException(QueryException::class);
        $lifer->sicknesses()->attach($first->id, ['contracted_at' => now()]);
    }
}
