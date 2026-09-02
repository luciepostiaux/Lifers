<?php

namespace Tests\Feature;

use App\Models\DailyJournalAccess;
use App\Models\FamilyChild;
use App\Models\Lifer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class DailyJournalTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_daily_journal_is_paid_once_and_remains_available_for_the_day(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(money: 10);

        $this->actingAs($user)
            ->get(route('city.journal.index'))
            ->assertRedirect(route('city'))
            ->assertSessionHasErrors('journal');

        $this->post(route('city.journal.purchase'))->assertRedirect(route('city.journal.index'));
        $this->assertSame('9.00', $lifer->gameState()->value('money'));

        $this->post(route('city.journal.purchase'))->assertRedirect(route('city.journal.index'));
        $this->assertSame('9.00', $lifer->gameState()->value('money'));
        $this->assertSame(1, DailyJournalAccess::count());

        $this->get(route('city.journal.index'))->assertOk();
    }

    public function test_obituary_lists_lifers_and_children_who_died_today(): void
    {
        [$reader, $readerLifer] = $this->createUserWithLifer();
        DailyJournalAccess::create([
            'lifer_id' => $readerLifer->id,
            'access_date' => today(),
            'price_paid' => 1,
            'purchased_at' => now(),
        ]);

        Lifer::withoutEvents(function () {
            Lifer::query()->create([
                'user_id' => User::factory()->create()->id,
                'first_name' => 'Charlie',
                'last_name' => 'Souvenir',
                'sex' => Lifer::SEX_FEMALE,
                'born_at' => now()->subDays(150),
                'status' => Lifer::STATUS_DEAD,
                'died_at' => now(),
                'age_at_death' => 68,
                'death_cause' => 'Mort naturelle liée à l’âge',
            ]);
        });
        FamilyChild::query()->create([
            'pregnancy_id' => null,
            'biological_mother_lifer_id' => null,
            'biological_father_lifer_id' => null,
            'claimed_lifer_id' => null,
            'birth_order' => 1,
            'first_name' => 'Lily',
            'last_name' => 'Souvenir',
            'sex' => Lifer::SEX_FEMALE,
            'status' => FamilyChild::STATUS_DEAD,
            'conceived_at' => now()->subDays(32),
            'born_at' => now()->subDays(30),
            'adult_at' => null,
            'died_at' => now(),
            'death_cause' => 'Négligence des besoins essentiels',
        ]);

        $this->actingAs($reader)
            ->get(route('city.journal.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('City/DailyJournal')
                ->has('deaths', 2)
                ->where('deaths.0.first_name', fn ($value) => in_array($value, ['Charlie', 'Lily'], true))
            );
    }

    public function test_a_new_day_requires_a_new_purchase(): void
    {
        [$user] = $this->createUserWithLifer(money: 10);

        $this->actingAs($user)->post(route('city.journal.purchase'));
        $this->travel(1)->days();

        $this->get(route('city.journal.index'))
            ->assertRedirect(route('city'))
            ->assertSessionHasErrors('journal');
    }
}
