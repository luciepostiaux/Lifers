<?php

namespace Tests\Feature;

use App\Broadcasting\ConversationChannel;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class ConversationAuthorizationTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_member_can_read_and_outsider_cannot_read_conversation_messages(): void
    {
        [$memberUser, $member] = $this->createUserWithLifer();
        [$outsiderUser] = $this->createUserWithLifer();
        $conversation = $this->privateConversation($member->id);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $member->id,
            'content' => 'Message réservé aux membres',
        ]);

        $this->actingAs($memberUser)
            ->get("/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonFragment(['content' => 'Message réservé aux membres']);
        $this->flushSession();
        $this->actingAs($outsiderUser)
            ->get("/conversations/{$conversation->id}/messages")
            ->assertForbidden();
    }

    public function test_visiting_social_joins_active_lifer_to_general_conversation(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();

        $response = $this->actingAs($user)->get('/social');
        $general = Conversation::where('key', 'general')->firstOrFail();

        $response->assertRedirect('/social/'.$general->id);
        $this->assertDatabaseHas('conversation_lifer', [
            'conversation_id' => $general->id,
            'lifer_id' => $lifer->id,
        ]);
    }

    public function test_private_conversation_is_reused_for_same_lifer_pair(): void
    {
        [$firstUser, $firstLifer] = $this->createUserWithLifer();
        [, $secondLifer] = $this->createUserWithLifer();

        $first = $this->actingAs($firstUser)->postJson('/conversations', ['lifer_id' => $secondLifer->id]);
        $second = $this->actingAs($firstUser)->postJson('/conversations', ['lifer_id' => $secondLifer->id]);

        $first->assertCreated();
        $second->assertOk();
        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertDatabaseHas('conversation_lifer', ['conversation_id' => $first->json('id'), 'lifer_id' => $firstLifer->id]);
        $this->assertDatabaseHas('conversation_lifer', ['conversation_id' => $first->json('id'), 'lifer_id' => $secondLifer->id]);
    }

    public function test_social_page_exposes_general_and_private_tabs_for_lifers(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [, $other] = $this->createUserWithLifer(900, [], 'Autre', 'Lifer');
        $general = Conversation::create(['name' => 'Général', 'type' => 'general', 'key' => 'general']);
        $general->lifers()->attach($lifer->id);
        $private = Conversation::create([
            'type' => 'private',
            'key' => Conversation::privateKey($lifer->id, $other->id),
        ]);
        $private->lifers()->attach([$lifer->id, $other->id]);

        $this->actingAs($user)
            ->get('/social/'.$private->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Social/Index')
                ->where('currentConversationId', $private->id)
                ->where('conversations.0.display_name', 'Général')
                ->where('conversations.1.display_name', 'Autre Lifer')
                ->where('currentLifer.id', $lifer->id));
    }

    public function test_only_members_can_send_and_message_length_is_limited(): void
    {
        Event::fake([MessageSent::class]);
        [$memberUser, $member] = $this->createUserWithLifer();
        [$outsiderUser, $outsider] = $this->createUserWithLifer();
        $conversation = $this->privateConversation($member->id);

        $this->actingAs($memberUser)
            ->post("/conversations/{$conversation->id}/messages", ['content' => 'Message autorisé'])
            ->assertOk();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $member->id,
            'content' => 'Message autorisé',
        ]);

        $this->flushSession();
        $this->actingAs($outsiderUser)
            ->post("/conversations/{$conversation->id}/messages", ['content' => 'Interdit'])
            ->assertForbidden();
        $this->assertDatabaseMissing('messages', ['sender_lifer_id' => $outsider->id]);

        $this->flushSession();
        $this->actingAs($memberUser)
            ->postJson("/conversations/{$conversation->id}/messages", ['content' => str_repeat('a', 2001)])
            ->assertJsonValidationErrors('content');
    }

    public function test_received_private_message_sets_navigation_alert_until_conversation_is_opened(): void
    {
        [$recipientUser, $recipient] = $this->createUserWithLifer();
        [, $sender] = $this->createUserWithLifer();
        $conversation = Conversation::create([
            'type' => Conversation::TYPE_PRIVATE,
            'key' => Conversation::privateKey($recipient->id, $sender->id),
        ]);
        $conversation->lifers()->attach([$recipient->id, $sender->id]);
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $sender->id,
            'content' => 'Un nouveau message privé',
        ]);

        $this->actingAs($recipientUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('unreadPrivateMessagesCount', 1));

        $this->get('/social/'.$conversation->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('unreadPrivateMessagesCount', 0));

        $this->assertDatabaseHas('message_reads', [
            'message_id' => $message->id,
            'reader_lifer_id' => $recipient->id,
        ]);

        $messageWhileOpen = Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $sender->id,
            'content' => 'Message reçu pendant l’ouverture',
        ]);

        $this->post('/conversations/'.$conversation->id.'/read')
            ->assertNoContent();

        $this->assertDatabaseHas('message_reads', [
            'message_id' => $messageWhileOpen->id,
            'reader_lifer_id' => $recipient->id,
        ]);
    }

    public function test_navigation_alert_ignores_own_general_and_group_messages(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [, $other] = $this->createUserWithLifer();

        foreach ([Conversation::TYPE_GENERAL, Conversation::TYPE_GROUP] as $type) {
            $conversation = Conversation::create([
                'name' => $type,
                'type' => $type,
                'key' => $type.':'.fake()->uuid(),
            ]);
            $conversation->lifers()->attach([$lifer->id, $other->id]);
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_lifer_id' => $other->id,
                'content' => 'Message hors MP',
            ]);
        }

        $private = Conversation::create([
            'type' => Conversation::TYPE_PRIVATE,
            'key' => Conversation::privateKey($lifer->id, $other->id),
        ]);
        $private->lifers()->attach([$lifer->id, $other->id]);
        Message::create([
            'conversation_id' => $private->id,
            'sender_lifer_id' => $lifer->id,
            'content' => 'Mon propre message',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('unreadPrivateMessagesCount', 0));
    }

    public function test_private_message_is_broadcast_to_recipient_account_channel(): void
    {
        [, $sender] = $this->createUserWithLifer();
        [$recipientUser, $recipient] = $this->createUserWithLifer();
        $conversation = Conversation::create([
            'type' => Conversation::TYPE_PRIVATE,
            'key' => Conversation::privateKey($sender->id, $recipient->id),
        ]);
        $conversation->lifers()->attach([$sender->id, $recipient->id]);
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_lifer_id' => $sender->id,
            'content' => 'Alerte en direct',
        ]);

        $channelNames = collect((new MessageSent($message))->broadcastOn())
            ->pluck('name')
            ->all();

        $this->assertContains('presence-conversation.'.$conversation->id, $channelNames);
        $this->assertContains('private-App.Models.User.'.$recipientUser->id, $channelNames);
    }

    public function test_lifer_can_create_a_named_group_with_selected_members(): void
    {
        [$creatorUser, $creator] = $this->createUserWithLifer();
        [, $member] = $this->createUserWithLifer(900, [], 'Membre', 'Choisi');
        [$outsiderUser] = $this->createUserWithLifer(900, [], 'Hors', 'Groupe');

        $response = $this->actingAs($creatorUser)->postJson('/conversations/groups', [
            'name' => 'Les voisins',
            'member_ids' => [$member->id],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Les voisins')
            ->assertJsonPath('type', Conversation::TYPE_GROUP);

        $groupId = $response->json('id');
        $this->assertDatabaseHas('conversation_lifer', [
            'conversation_id' => $groupId,
            'lifer_id' => $creator->id,
        ]);
        $this->assertDatabaseHas('conversation_lifer', [
            'conversation_id' => $groupId,
            'lifer_id' => $member->id,
        ]);

        $this->flushSession();
        $this->actingAs($outsiderUser)
            ->get("/conversations/{$groupId}/messages")
            ->assertForbidden();
    }

    public function test_group_members_can_add_lifers_and_leave_safely(): void
    {
        [$creatorUser, $creator] = $this->createUserWithLifer();
        [$memberUser, $member] = $this->createUserWithLifer();
        [$newMemberUser, $newMember] = $this->createUserWithLifer();
        $group = Conversation::create([
            'name' => 'Groupe de test',
            'type' => Conversation::TYPE_GROUP,
            'key' => 'group:'.fake()->uuid(),
        ]);
        $group->lifers()->attach([$creator->id, $member->id]);

        $this->actingAs($memberUser)
            ->postJson("/conversations/{$group->id}/members", [
                'member_ids' => [$newMember->id],
            ])
            ->assertOk();
        $this->assertDatabaseHas('conversation_lifer', [
            'conversation_id' => $group->id,
            'lifer_id' => $newMember->id,
        ]);

        $this->flushSession();
        $this->actingAs($newMemberUser)
            ->deleteJson("/conversations/{$group->id}/members/me")
            ->assertOk();
        $this->assertDatabaseMissing('conversation_lifer', [
            'conversation_id' => $group->id,
            'lifer_id' => $newMember->id,
        ]);
        $this->assertDatabaseHas('conversations', ['id' => $group->id]);

        $this->flushSession();
        $this->actingAs($memberUser)
            ->deleteJson("/conversations/{$group->id}/members/me")
            ->assertOk();
        $this->flushSession();
        $this->actingAs($creatorUser)
            ->deleteJson("/conversations/{$group->id}/members/me")
            ->assertOk();
        $this->assertDatabaseMissing('conversations', ['id' => $group->id]);
    }

    public function test_outsider_cannot_add_members_and_private_conversation_cannot_be_left_as_a_group(): void
    {
        [$memberUser, $member] = $this->createUserWithLifer();
        [$outsiderUser, $outsider] = $this->createUserWithLifer();
        $group = Conversation::create([
            'name' => 'Groupe protégé',
            'type' => Conversation::TYPE_GROUP,
            'key' => 'group:'.fake()->uuid(),
        ]);
        $group->lifers()->attach($member->id);

        $this->actingAs($outsiderUser)
            ->postJson("/conversations/{$group->id}/members", [
                'member_ids' => [$outsider->id],
            ])
            ->assertForbidden();

        $private = $this->privateConversation($member->id);
        $this->flushSession();
        $this->actingAs($memberUser)
            ->deleteJson("/conversations/{$private->id}/members/me")
            ->assertForbidden();
    }

    public function test_presence_channel_exposes_lifer_identity_only_to_members(): void
    {
        [$memberUser, $member] = $this->createUserWithLifer();
        [$outsiderUser] = $this->createUserWithLifer();
        $conversation = $this->privateConversation($member->id);

        $this->assertFalse(app(ConversationChannel::class)->join($outsiderUser, $conversation));
        $presence = app(ConversationChannel::class)->join($memberUser, $conversation);
        $this->assertSame($member->id, $presence['id']);
    }

    private function privateConversation(int $memberId): Conversation
    {
        $conversation = Conversation::create([
            'name' => null,
            'type' => Conversation::TYPE_PRIVATE,
            'key' => 'test:'.fake()->uuid(),
        ]);
        $conversation->lifers()->attach($memberId);

        return $conversation;
    }
}
