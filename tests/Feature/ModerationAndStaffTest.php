<?php

namespace Tests\Feature;

use App\Broadcasting\ConversationChannel;
use App\Events\MessageDeleted;
use App\Models\AdminAuditLog;
use App\Models\Conversation;
use App\Models\Lifer;
use App\Models\LiferImage;
use App\Models\Message;
use App\Models\ProfileComment;
use App\Models\Role;
use App\Models\Sickness;
use App\Models\User;
use App\Services\LiferLifecycleService;
use App\Services\NaturalMortalityService;
use App\Services\SicknessProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class ModerationAndStaffTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_only_staff_can_open_moderation_and_moderator_remains_a_player(): void
    {
        [$moderator, $moderatorLifer] = $this->createUserWithLifer(firstName: 'Jordan', lastName: 'Dells');
        $this->giveRole($moderator, Role::MODERATOR);
        [$ordinaryUser, $target] = $this->createUserWithLifer();

        $this->actingAs($ordinaryUser)
            ->get(route('moderation.dashboard'))
            ->assertForbidden();

        $this->flushSession();
        $this->actingAs($moderator)
            ->get(route('moderation.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Moderation/Dashboard')
                ->where('permissions.admin', false)
                ->where('permissions.moderate', true)
                ->where('security.idle_timeout_minutes', 15)
                ->where('lifer.staff_role', Role::MODERATOR));

        $this->actingAs($moderator)
            ->patch(route('admin.lifers.money.update', $target), [
                'amount' => 100,
                'reason' => 'Action interdite au rôle de modération.',
            ])
            ->assertForbidden();

        app(LiferLifecycleService::class)->die($moderatorLifer, 'Fin de vie de test');
        $newLifer = Lifer::factory()->for($moderator)->create([
            'first_name' => 'Jordan',
            'last_name' => 'Nouveau',
        ]);

        $this->assertTrue($moderator->fresh()->hasRole(Role::MODERATOR));
        $this->assertFalse($newLifer->isDeathProtected());

        $this->flushSession();
        $this->actingAs($moderator->fresh())
            ->get(route('moderation.dashboard'))
            ->assertOk();
    }

    public function test_moderator_can_remove_public_content_but_not_manage_roles(): void
    {
        Event::fake([MessageDeleted::class]);
        Storage::fake('public');

        [$moderator] = $this->createUserWithLifer(firstName: 'Mina', lastName: 'Veille');
        $this->giveRole($moderator, Role::MODERATOR);
        [, $profileOwner] = $this->createUserWithLifer(firstName: 'Profil', lastName: 'Public');
        [, $commentAuthor] = $this->createUserWithLifer(firstName: 'Auteur', lastName: 'Public');

        $profileOwner->profile()->create([
            'content' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'Ancienne présentation']],
                ]],
            ],
            'show_money' => false,
        ]);
        Storage::disk('public')->put('lifer-profiles/image-a-retirer.jpg', 'image');
        $image = LiferImage::query()->create([
            'lifer_id' => $profileOwner->id,
            'image_path' => 'lifer-profiles/image-a-retirer.jpg',
        ]);
        $comment = ProfileComment::query()->create([
            'author_lifer_id' => $commentAuthor->id,
            'receiver_lifer_id' => $profileOwner->id,
            'content' => 'Commentaire public abusif',
            'status' => ProfileComment::STATUS_APPROVED,
        ]);
        $conversation = Conversation::query()->create([
            'name' => 'Salon général',
            'type' => Conversation::TYPE_GENERAL,
            'key' => 'general:moderation-test',
        ]);
        $conversation->lifers()->attach([$profileOwner->id, $commentAuthor->id]);
        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $commentAuthor->id,
            'content' => 'Message public abusif',
        ]);

        $newContent = [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'Présentation corrigée']],
            ]],
        ];

        $this->actingAs($moderator)
            ->patch(route('moderation.profiles.update', $profileOwner), [
                'content' => $newContent,
                'reason' => 'Retrait du contenu inadapté.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('Présentation corrigée', $profileOwner->profile->fresh()->content['content'][0]['content'][0]['text']);
        $this->assertDatabaseMissing('lifer_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing('lifer-profiles/image-a-retirer.jpg');

        $this->actingAs($moderator)
            ->delete(route('moderation.comments.destroy', $comment), [
                'reason' => 'Commentaire contraire aux règles.',
            ])
            ->assertSessionHas('success');
        $this->actingAs($moderator)
            ->delete(route('moderation.messages.destroy', $message), [
                'reason' => 'Message contraire aux règles.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('profile_comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
        $this->assertSame(3, AdminAuditLog::query()->where('actor_user_id', $moderator->id)->count());
        Event::assertDispatched(MessageDeleted::class);

        $this->actingAs($moderator)
            ->get(route('moderation.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('recentActions.0.label', 'Message du salon général supprimé')
                ->where('recentActions.0.removed_text', 'Message public abusif')
                ->where('recentActions.0.source', 'Auteur Public')
                ->where('recentActions.0.reason', 'Message contraire aux règles.'));

        $this->actingAs($moderator)
            ->patch(route('admin.users.role.update', $profileOwner->user), [
                'role' => Role::MODERATOR,
            ])
            ->assertForbidden();
    }

    public function test_private_and_custom_group_messages_are_excluded_from_moderation_and_remain_member_only(): void
    {
        [$moderator] = $this->createUserWithLifer(firstName: 'Mina', lastName: 'Veille');
        $this->giveRole($moderator, Role::MODERATOR);
        [$admin] = $this->createUserWithLifer(firstName: 'Lucie', lastName: 'Admin');
        $this->giveRole($admin, Role::ADMIN);
        [, $firstLifer] = $this->createUserWithLifer(firstName: 'Privé', lastName: 'Un');
        [, $secondLifer] = $this->createUserWithLifer(firstName: 'Privé', lastName: 'Deux');

        $general = Conversation::query()->create([
            'name' => 'Salon général',
            'type' => Conversation::TYPE_GENERAL,
            'key' => 'general:privacy-test',
        ]);
        $private = Conversation::query()->create([
            'name' => null,
            'type' => Conversation::TYPE_PRIVATE,
            'key' => Conversation::privateKey($firstLifer->id, $secondLifer->id),
        ]);
        $group = Conversation::query()->create([
            'name' => 'Groupe entre amis',
            'type' => Conversation::TYPE_GROUP,
            'key' => 'group:privacy-test',
        ]);
        $general->lifers()->attach([$firstLifer->id, $secondLifer->id]);
        $private->lifers()->attach([$firstLifer->id, $secondLifer->id]);
        $group->lifers()->attach([$firstLifer->id, $secondLifer->id]);

        $generalMessage = Message::query()->create([
            'conversation_id' => $general->id,
            'sender_lifer_id' => $firstLifer->id,
            'content' => 'Message visible dans le salon général',
        ]);
        $privateMessage = Message::query()->create([
            'conversation_id' => $private->id,
            'sender_lifer_id' => $firstLifer->id,
            'content' => 'Message privé invisible à l’équipe',
        ]);
        $groupMessage = Message::query()->create([
            'conversation_id' => $group->id,
            'sender_lifer_id' => $secondLifer->id,
            'content' => 'Message de groupe invisible à l’équipe',
        ]);

        $this->actingAs($moderator)
            ->get(route('moderation.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('communityMessages', 1)
                ->where('communityMessages.0.id', $generalMessage->id)
                ->where('communityMessages.0.content', 'Message visible dans le salon général'));

        $this->actingAs($moderator)
            ->delete(route('moderation.messages.destroy', $privateMessage), [
                'reason' => 'Cette intervention doit être impossible.',
            ])
            ->assertNotFound();

        $this->flushSession();
        $this->actingAs($admin)
            ->getJson("/conversations/{$private->id}/messages")
            ->assertForbidden();
        $this->actingAs($admin)
            ->getJson("/conversations/{$group->id}/messages")
            ->assertForbidden();
        $this->actingAs($admin)
            ->delete(route('moderation.messages.destroy', $groupMessage), [
                'reason' => 'Cette intervention doit être impossible.',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('messages', ['id' => $privateMessage->id]);
        $this->assertDatabaseHas('messages', ['id' => $groupMessage->id]);
        $this->assertSame(0, AdminAuditLog::query()->count());
    }

    public function test_moderation_history_describes_the_action_and_the_exact_profile_text_before_and_after(): void
    {
        [$moderator] = $this->createUserWithLifer(firstName: 'Mina', lastName: 'Veille');
        $this->giveRole($moderator, Role::MODERATOR);
        [, $profileOwner] = $this->createUserWithLifer(firstName: 'Profil', lastName: 'Public');
        $profileOwner->profile()->create([
            'content' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'Texte public avant modération.']],
                ]],
            ],
            'show_money' => false,
        ]);

        $this->actingAs($moderator)
            ->patch(route('moderation.profiles.update', $profileOwner), [
                'content' => [
                    'type' => 'doc',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Texte public après modération.']],
                    ]],
                ],
                'reason' => 'Retrait précis du passage problématique.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($moderator)
            ->get(route('moderation.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('recentActions.0.label', 'Présentation de profil modifiée')
                ->where('recentActions.0.target', 'Profil Public')
                ->where('recentActions.0.before_text', 'Texte public avant modération.')
                ->where('recentActions.0.after_text', 'Texte public après modération.')
                ->where('recentActions.0.reason', 'Retrait précis du passage problématique.'));
    }

    public function test_staff_chat_is_private_real_time_and_uses_only_lifer_identity(): void
    {
        [$moderator, $moderatorLifer] = $this->createUserWithLifer(firstName: 'Jordan', lastName: 'Dells');
        $this->giveRole($moderator, Role::MODERATOR);
        [$admin, $adminLifer] = $this->createUserWithLifer(firstName: 'Lucie', lastName: 'Admin');
        $this->giveRole($admin, Role::ADMIN);
        [$ordinaryUser, $ordinaryLifer] = $this->createUserWithLifer(firstName: 'Personne', lastName: 'Joueuse');

        $this->actingAs($moderator)
            ->get(route('moderation.dashboard'))
            ->assertOk();

        $staffConversation = Conversation::query()->where('key', Conversation::KEY_STAFF)->firstOrFail();
        $this->assertTrue($staffConversation->lifers()->whereKey($moderatorLifer->id)->exists());
        $this->assertTrue($staffConversation->lifers()->whereKey($adminLifer->id)->exists());
        $this->assertFalse($staffConversation->lifers()->whereKey($ordinaryLifer->id)->exists());

        $response = $this->actingAs($moderator)
            ->postJson("/conversations/{$staffConversation->id}/messages", [
                'content' => 'Message réservé à l’équipe',
            ])
            ->assertOk()
            ->assertJsonPath('sender.first_name', 'Jordan')
            ->assertJsonPath('sender.staff_role', Role::MODERATOR)
            ->assertJsonMissingPath('sender.user')
            ->assertJsonMissing(['email' => $moderator->email]);

        $this->assertDatabaseHas('messages', [
            'id' => $response->json('id'),
            'conversation_id' => $staffConversation->id,
        ]);

        $this->flushSession();
        $this->actingAs($ordinaryUser)
            ->getJson("/conversations/{$staffConversation->id}/messages")
            ->assertForbidden();
        $this->actingAs($ordinaryUser)
            ->postJson("/conversations/{$staffConversation->id}/messages", ['content' => 'Intrusion'])
            ->assertForbidden();

        $channel = app(ConversationChannel::class);
        $this->assertFalse($channel->join($ordinaryUser, $staffConversation));
        $this->assertSame(Role::MODERATOR, $channel->join($moderator, $staffConversation)['staff_role']);
    }

    public function test_staff_role_is_visible_on_public_profile_and_comments_without_user_identity(): void
    {
        [$moderator, $moderatorLifer] = $this->createUserWithLifer(firstName: 'Jordan', lastName: 'Dells');
        $this->giveRole($moderator, Role::MODERATOR);
        [$visitor, $visitorLifer] = $this->createUserWithLifer(firstName: 'Visite', lastName: 'Profil');
        ProfileComment::query()->create([
            'author_lifer_id' => $moderatorLifer->id,
            'receiver_lifer_id' => $visitorLifer->id,
            'content' => 'Bienvenue sur ton profil.',
            'status' => ProfileComment::STATUS_APPROVED,
        ]);

        $this->actingAs($visitor)
            ->get(route('lifers.profile.show', $moderatorLifer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('profileLifer.staff_role', Role::MODERATOR)
                ->missing('profileLifer.user'));

        $this->actingAs($visitor)
            ->get(route('lifers.profile.show', $visitorLifer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('comments.0.author.name', 'Jordan Dells')
                ->where('comments.0.author.staff_role', Role::MODERATOR)
                ->missing('comments.0.author.user'));
    }

    public function test_an_administrator_lifer_cannot_die_from_any_implemented_cause(): void
    {
        [$admin, $adminLifer] = $this->createUserWithLifer(firstName: 'Lucie', lastName: 'Admin');
        $this->giveRole($admin, Role::ADMIN);

        try {
            app(LiferLifecycleService::class)->die($adminLifer, 'Cause directe');
            $this->fail('La protection contre la mort directe aurait dû être appliquée.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lifer', $exception->errors());
        }

        $adminLifer->update(['born_at' => now()->subDays((110 - 18) * 3)]);
        $mortalityResult = app(NaturalMortalityService::class)->processAll();
        $this->assertSame(0, $mortalityResult['natural_deaths']);
        $this->assertSame(Lifer::STATUS_ACTIVE, $adminLifer->fresh()->status);

        $fatalSickness = Sickness::query()->create([
            'name' => 'Maladie fatale de test',
            'slug' => 'maladie-fatale-admin',
            'description' => 'Vérifie la protection du compte administrateur.',
            'duration_days' => 30,
            'fatal_after_days' => 1,
            'type' => 'random',
            'self_resolving' => false,
        ]);
        $adminLifer->sicknesses()->attach($fatalSickness->id, [
            'contracted_at' => now()->subDays(2),
            'fatal_at' => now()->subDay(),
        ]);

        $sicknessResult = app(SicknessProgressionService::class)->processAll();
        $this->assertSame(0, $sicknessResult['deaths']);
        $this->assertSame(Lifer::STATUS_ACTIVE, $adminLifer->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.lifers.kill', $adminLifer), [
                'cause' => 'Action administrative',
                'reason' => 'Cette action doit être refusée.',
            ])
            ->assertSessionHasErrors('lifer');
        $this->assertSame(Lifer::STATUS_ACTIVE, $adminLifer->fresh()->status);
    }

    public function test_idle_session_configuration_and_keep_alive_endpoint_are_protected(): void
    {
        $this->assertSame(15, (int) config('session.lifetime'));

        $this->postJson(route('session.keep-alive'))->assertUnauthorized();

        $user = User::factory()->create();
        $this->actingAs($user)
            ->postJson(route('session.keep-alive'))
            ->assertNoContent();
    }

    private function giveRole(User $user, string $name): void
    {
        $role = Role::query()->firstOrCreate(['name' => $name]);
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->unsetRelation('roles');
    }
}
