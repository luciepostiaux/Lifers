<?php

namespace Tests\Feature;

use App\Models\BodyType;
use App\Models\FamilyChild;
use App\Models\Lifer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class CharacterReincarnationTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_character_page_lists_only_adult_unclaimed_family_identities(): void
    {
        $this->bodyTypes();
        [, $mother] = $this->createUserWithLifer(firstName: 'Ava', lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(firstName: 'Noé', lastName: 'Martin');
        $available = $this->familyChild($mother, $father, [
            'first_name' => 'Lou',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_AVAILABLE,
            'born_at' => now()->subDays(80),
            'adult_at' => now()->subDay(),
        ]);
        $this->familyChild($mother, $father, [
            'first_name' => 'Mia',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_DEPENDENT,
            'born_at' => now()->subDays(30),
            'adult_at' => now()->addDays(24),
        ]);
        $this->familyChild($mother, $father, [
            'first_name' => 'Léo',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_AVAILABLE,
            'born_at' => now()->subDays(53),
            'adult_at' => now()->addDay(),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('character.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Character/Create')
                ->has('bodyTypes', 2)
                ->has('availableFamilyLifers', 1)
                ->where('availableFamilyLifers.0.id', $available->id)
                ->where('availableFamilyLifers.0.first_name', 'Lou')
                ->where('availableFamilyLifers.0.last_name', 'Martin')
                ->where('availableFamilyLifers.0.sex', Lifer::SEX_FEMALE));

        $this->assertSame(18, $available->calculateAge());
    }

    public function test_available_family_identity_can_be_reincarnated_without_losing_filiation(): void
    {
        [, $femaleBody] = $this->bodyTypes();
        [, $mother] = $this->createUserWithLifer(firstName: 'Ava', lastName: 'Rivière');
        [, $father] = $this->createUserWithLifer(firstName: 'Noé', lastName: 'Martin');
        $child = $this->familyChild($mother, $father, [
            'first_name' => 'Lou',
            'last_name' => 'Martin',
            'status' => FamilyChild::STATUS_AVAILABLE,
            'born_at' => now()->subDays(80),
            'adult_at' => now()->subDay(),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('character.store'), [
                'creation_mode' => 'reincarnation',
                'family_child_id' => $child->id,
                'body_type_id' => $femaleBody->id,
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();

        $lifer = $user->lifers()->firstOrFail();

        $this->assertSame('Lou', $lifer->first_name);
        $this->assertSame('Martin', $lifer->last_name);
        $this->assertSame(Lifer::SEX_FEMALE, $lifer->sex);
        $this->assertSame(18, $lifer->calculateAge());
        $this->assertSame('900.00', $lifer->gameState->money);
        $this->assertSame($femaleBody->id, $lifer->gameState->body_type_id);
        $this->assertNotNull($lifer->lifeGauge);
        $this->assertNotNull($lifer->inventory);
        $this->assertCount(0, $lifer->inventory->items);

        $this->assertDatabaseHas('family_children', [
            'id' => $child->id,
            'status' => FamilyChild::STATUS_CLAIMED,
            'claimed_lifer_id' => $lifer->id,
            'biological_mother_lifer_id' => $mother->id,
            'biological_father_lifer_id' => $father->id,
        ]);
        $this->assertDatabaseHas('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $mother->id,
            'type' => 'biological',
        ]);
        $this->assertDatabaseHas('family_child_guardians', [
            'child_id' => $child->id,
            'lifer_id' => $father->id,
            'type' => 'biological',
        ]);

        $this->actingAs($user)
            ->get(route('family.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('parents', 2)
                ->where('parents.0.role', 'Mère')
                ->where('parents.0.name', 'Ava Rivière')
                ->where('parents.1.role', 'Père')
                ->where('parents.1.name', 'Noé Martin'));
    }

    public function test_reincarnation_rejects_an_incompatible_appearance_and_an_already_claimed_identity(): void
    {
        [$maleBody, $femaleBody] = $this->bodyTypes();
        [, $mother] = $this->createUserWithLifer();
        [, $father] = $this->createUserWithLifer();
        $child = $this->familyChild($mother, $father, [
            'status' => FamilyChild::STATUS_AVAILABLE,
            'born_at' => now()->subDays(54),
            'adult_at' => now()->subMinute(),
        ]);
        $firstUser = User::factory()->create();

        $this->actingAs($firstUser)
            ->post(route('character.store'), [
                'creation_mode' => 'reincarnation',
                'family_child_id' => $child->id,
                'body_type_id' => $maleBody->id,
            ])
            ->assertSessionHasErrors('body_type_id');

        $this->assertSame(FamilyChild::STATUS_AVAILABLE, $child->fresh()->status);
        $this->assertFalse($firstUser->lifers()->exists());

        $this->actingAs($firstUser)
            ->post(route('character.store'), [
                'creation_mode' => 'reincarnation',
                'family_child_id' => $child->id,
                'body_type_id' => $femaleBody->id,
            ])
            ->assertRedirect(route('dashboard'));

        $secondUser = User::factory()->create();
        $this->flushSession();

        $this->actingAs($secondUser)
            ->post(route('character.store'), [
                'creation_mode' => 'reincarnation',
                'family_child_id' => $child->id,
                'body_type_id' => $femaleBody->id,
            ])
            ->assertSessionHasErrors('family_child_id');

        $this->assertFalse($secondUser->lifers()->exists());
        $this->assertSame(1, Lifer::query()->where('id', $child->fresh()->claimed_lifer_id)->count());
    }

    public function test_standard_character_creation_still_uses_the_chosen_identity_and_appearance(): void
    {
        [$maleBody] = $this->bodyTypes();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('character.store'), [
                'creation_mode' => 'new',
                'first_name' => 'Camille',
                'last_name' => 'Démo',
                'body_type_id' => $maleBody->id,
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lifers', [
            'user_id' => $user->id,
            'first_name' => 'Camille',
            'last_name' => 'Démo',
            'sex' => Lifer::SEX_MALE,
            'status' => Lifer::STATUS_ACTIVE,
        ]);
    }

    private function bodyTypes(): array
    {
        $male = BodyType::query()->firstOrCreate(
            ['code' => BodyType::CODE_MALE],
            [
                'label' => 'Corps A',
                'sex' => Lifer::SEX_MALE,
                'image_path' => 'images/perso/body-a.png',
            ],
        );
        $female = BodyType::query()->firstOrCreate(
            ['code' => BodyType::CODE_FEMALE],
            [
                'label' => 'Corps B',
                'sex' => Lifer::SEX_FEMALE,
                'image_path' => 'images/perso/body-b.png',
            ],
        );

        return [$male, $female];
    }

    private function familyChild(Lifer $mother, Lifer $father, array $attributes = []): FamilyChild
    {
        $child = FamilyChild::query()->create(array_merge([
            'biological_mother_lifer_id' => $mother->id,
            'biological_father_lifer_id' => $father->id,
            'birth_order' => 1,
            'first_name' => 'Lou',
            'last_name' => 'Martin',
            'sex' => Lifer::SEX_FEMALE,
            'status' => FamilyChild::STATUS_AVAILABLE,
            'conceived_at' => now()->subDays(56),
            'born_at' => now()->subDays(54),
            'adult_at' => now(),
        ], $attributes));

        $child->guardians()->attach([
            $mother->id => ['type' => 'biological', 'has_custody' => false],
            $father->id => ['type' => 'biological', 'has_custody' => false],
        ]);

        return $child;
    }
}
