<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\LiferImage;
use App\Models\LiferMarriage;
use App\Models\LiferProfile;
use App\Models\ProfileComment;
use App\Services\LiferLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class LiferProfileTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_profile_exposes_private_information_only_to_its_owner(): void
    {
        [$ownerUser, $owner] = $this->createUserWithLifer(1234, [], 'Camille', 'Démo');
        [$visitorUser] = $this->createUserWithLifer();
        $diploma = Diploma::create(['name' => 'Diplôme visible', 'description' => 'Test']);
        $owner->diplomas()->attach($diploma->id, ['earned_at' => now(), 'is_public' => false]);

        $this->actingAs($ownerUser)
            ->get('/profil')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProfilPerso/Index')
                ->where('isOwner', true)
                ->where('profileLifer.money', '1234.00')
                ->has('diplomas', 1));

        $this->flushSession();
        $this->actingAs($visitorUser)
            ->get(route('lifers.profile.show', $owner))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('isOwner', false)
                ->where('profileLifer.money', null)
                ->has('diplomas', 0));
    }

    public function test_owner_can_update_presentation_money_and_diploma_visibility(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $diploma = Diploma::create(['name' => 'Diplôme choisi', 'description' => 'Test']);
        $lifer->diplomas()->attach($diploma->id, ['earned_at' => now(), 'is_public' => false]);
        $content = [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Bonjour depuis mon profil',
                    'marks' => [
                        ['type' => 'bold'],
                        ['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']],
                    ],
                ]],
            ]],
        ];

        $this->actingAs($user)
            ->put(route('profil.update'), [
                'content' => $content,
                'show_money' => true,
                'public_diploma_ids' => [$diploma->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lifer_profiles', [
            'lifer_id' => $lifer->id,
            'show_money' => true,
        ]);
        $this->assertDatabaseHas('lifer_diplomas', [
            'lifer_id' => $lifer->id,
            'diploma_id' => $diploma->id,
            'is_public' => true,
        ]);

        $stored = $lifer->profile()->firstOrFail()->content;
        $this->assertSame('bold', $stored['content'][0]['content'][0]['marks'][0]['type']);
        $this->assertCount(1, $stored['content'][0]['content'][0]['marks']);
    }

    public function test_profile_rejects_an_image_not_uploaded_for_the_current_lifer(): void
    {
        [$user] = $this->createUserWithLifer();

        $this->actingAs($user)
            ->put(route('profil.update'), [
                'content' => [
                    'type' => 'doc',
                    'content' => [[
                        'type' => 'image',
                        'attrs' => ['src' => 'https://example.com/tracker.png'],
                    ]],
                ],
                'show_money' => false,
                'public_diploma_ids' => [],
            ])
            ->assertSessionHasErrors('content');
    }

    public function test_owner_can_publicly_display_real_spouse_on_profile(): void
    {
        [$ownerUser, $owner] = $this->createUserWithLifer(900, [], 'Camille', 'Rivière');
        [, $spouse] = $this->createUserWithLifer(900, [], 'Lou', 'Martin');
        [$visitorUser] = $this->createUserWithLifer();
        LiferMarriage::create([
            'first_lifer_id' => $owner->id,
            'second_lifer_id' => $spouse->id,
            'lower_lifer_id' => min($owner->id, $spouse->id),
            'higher_lifer_id' => max($owner->id, $spouse->id),
            'status' => LiferMarriage::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        $this->actingAs($ownerUser)
            ->put(route('profil.update'), [
                'content' => null,
                'show_money' => false,
                'relationship_status' => LiferProfile::RELATIONSHIP_MARRIED_WITH,
                'public_diploma_ids' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lifer_profiles', [
            'lifer_id' => $owner->id,
            'relationship_status' => LiferProfile::RELATIONSHIP_MARRIED_WITH,
        ]);

        $this->flushSession();
        $this->actingAs($visitorUser)
            ->get(route('lifers.profile.show', $owner))
            ->assertInertia(fn (Assert $page) => $page
                ->where('profileLifer.relationship.label', 'Marié·e avec')
                ->where('profileLifer.relationship.spouse.id', $spouse->id)
                ->where('profileLifer.relationship.spouse.name', 'Lou Martin'));
    }

    public function test_profile_cannot_display_spouse_without_active_marriage(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();

        $this->actingAs($user)
            ->put(route('profil.update'), [
                'content' => null,
                'show_money' => false,
                'relationship_status' => LiferProfile::RELATIONSHIP_MARRIED_WITH,
                'public_diploma_ids' => [],
            ])
            ->assertSessionHasErrors('relationship_status');

        $this->assertDatabaseMissing('lifer_profiles', [
            'lifer_id' => $lifer->id,
            'relationship_status' => LiferProfile::RELATIONSHIP_MARRIED_WITH,
        ]);
    }

    public function test_comment_requires_owner_approval_and_is_visible_to_its_author_while_pending(): void
    {
        [$ownerUser, $owner] = $this->createUserWithLifer(900, [], 'Propriétaire', 'Profil');
        [$authorUser, $author] = $this->createUserWithLifer(900, [], 'Auteur', 'Commentaire');
        [$outsiderUser] = $this->createUserWithLifer();

        $this->actingAs($authorUser)
            ->post(route('lifers.profile.comments.store', $owner), ['content' => 'À valider'])
            ->assertRedirect();

        $comment = ProfileComment::firstOrFail();
        $this->assertSame(ProfileComment::STATUS_PENDING, $comment->status);

        $this->actingAs($authorUser)
            ->get(route('lifers.profile.show', $owner))
            ->assertInertia(fn (Assert $page) => $page->has('comments', 1));

        $this->flushSession();
        $this->actingAs($outsiderUser)
            ->get(route('lifers.profile.show', $owner))
            ->assertInertia(fn (Assert $page) => $page->has('comments', 0));

        $this->flushSession();
        $this->actingAs($ownerUser)
            ->patch(route('profil.comments.approve', $comment))
            ->assertRedirect();

        $this->assertDatabaseHas('profile_comments', [
            'id' => $comment->id,
            'status' => ProfileComment::STATUS_APPROVED,
        ]);

        $this->flushSession();
        $this->actingAs($outsiderUser)
            ->get(route('lifers.profile.show', $owner))
            ->assertInertia(fn (Assert $page) => $page->has('comments', 1));
    }

    public function test_only_comment_author_or_profile_owner_can_delete_it(): void
    {
        [$ownerUser, $owner] = $this->createUserWithLifer();
        [, $author] = $this->createUserWithLifer();
        [$outsiderUser] = $this->createUserWithLifer();
        $comment = ProfileComment::create([
            'author_lifer_id' => $author->id,
            'receiver_lifer_id' => $owner->id,
            'content' => 'Protégé',
            'status' => ProfileComment::STATUS_APPROVED,
        ]);

        $this->actingAs($outsiderUser)
            ->delete(route('profil.comments.destroy', $comment))
            ->assertForbidden();
        $this->assertDatabaseHas('profile_comments', ['id' => $comment->id]);

        $this->flushSession();
        $this->actingAs($ownerUser)
            ->delete(route('profil.comments.destroy', $comment))
            ->assertRedirect();
        $this->assertDatabaseMissing('profile_comments', ['id' => $comment->id]);
    }

    public function test_profile_image_upload_is_scoped_and_deleted_with_lifer_life(): void
    {
        Storage::fake('public');
        [$user, $lifer] = $this->createUserWithLifer();
        [, $other] = $this->createUserWithLifer();

        $response = $this->actingAs($user)
            ->postJson(route('profil.images.store'), [
                'image' => UploadedFile::fake()->image('presentation.jpg', 1200, 900),
            ])
            ->assertCreated();

        $image = LiferImage::findOrFail($response->json('id'));
        $this->assertSame($lifer->id, $image->lifer_id);
        Storage::disk('public')->assertExists($image->image_path);

        $lifer->profile()->create([
            'content' => ['type' => 'doc', 'content' => []],
            'show_money' => false,
        ]);
        $comment = ProfileComment::create([
            'author_lifer_id' => $other->id,
            'receiver_lifer_id' => $lifer->id,
            'content' => 'Supprimé avec cette vie',
            'status' => ProfileComment::STATUS_APPROVED,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('profil.images.destroy', LiferImage::create([
                'lifer_id' => $other->id,
                'image_path' => 'lifer-profiles/'.$other->id.'/other.jpg',
            ])))
            ->assertForbidden();

        app(LiferLifecycleService::class)->die($lifer, 'Fin du test');
        Storage::disk('public')->assertMissing($image->image_path);
        $this->assertDatabaseMissing('lifer_images', ['id' => $image->id]);
        $this->assertDatabaseMissing('lifer_profiles', ['lifer_id' => $lifer->id]);
        $this->assertDatabaseMissing('profile_comments', ['id' => $comment->id]);
    }
}
