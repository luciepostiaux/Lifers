<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Models\AdminAuditLog;
use App\Models\Conversation;
use App\Models\Lifer;
use App\Models\LiferImage;
use App\Models\Message;
use App\Models\ProfileComment;
use App\Models\Role;
use App\Models\User;
use App\Services\ProfileContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ModerationController extends Controller
{
    public function index(Request $request): Response
    {
        $staffLifer = $this->activeLifer(['gameState', 'user.roles']);
        $search = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $staffConversation = $this->synchronizeStaffConversation();

        $profiles = Lifer::query()
            ->active()
            ->whereHas('gameState')
            ->with(['user.roles', 'profile', 'profileImages'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }, function ($query): void {
                $query->where(function ($query): void {
                    $query->whereHas('profile', fn ($query) => $query->whereNotNull('content'))
                        ->orWhereHas('profileImages');
                });
            })
            ->latest('updated_at')
            ->limit(40)
            ->get()
            ->map(fn (Lifer $lifer) => [
                'id' => $lifer->id,
                'name' => "{$lifer->first_name} {$lifer->last_name}",
                'staff_role' => $lifer->staffRole(),
                'content' => $lifer->profile?->content,
                'images' => $lifer->profileImages->map(fn (LiferImage $image) => [
                    'id' => $image->id,
                    'url' => '/storage/'.$image->image_path,
                ])->values(),
            ]);

        $comments = ProfileComment::query()
            ->with(['author.user.roles', 'receiver.user.roles'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('content', 'like', "%{$search}%")
                        ->orWhereHas('author', fn ($query) => $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('receiver', fn ($query) => $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->limit(60)
            ->get()
            ->map(fn (ProfileComment $comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'status' => $comment->status,
                'created_at' => $comment->created_at?->toIso8601String(),
                'author' => $this->liferIdentity($comment->author),
                'receiver' => $this->liferIdentity($comment->receiver),
            ]);

        $communityMessages = Message::query()
            ->whereHas('conversation', fn ($query) => $query
                ->where('type', Conversation::TYPE_GENERAL))
            ->with(['sender.user.roles', 'conversation:id,name,type,key'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('content', 'like', "%{$search}%")
                        ->orWhereHas('sender', fn ($query) => $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->limit(60)
            ->get()
            ->map(fn (Message $message) => [
                ...$message->communityPayload(),
                'conversation_name' => $this->conversationName($message->conversation),
            ]);

        $staffMessages = $staffConversation->messages()
            ->with('sender.user.roles')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $message) => $message->communityPayload());

        return Inertia::render('Moderation/Dashboard', [
            'lifer' => [
                'id' => $staffLifer->id,
                'first_name' => $staffLifer->first_name,
                'last_name' => $staffLifer->last_name,
                'staff_role' => $staffLifer->staffRole(),
            ],
            'money' => $staffLifer->gameState?->money,
            'filters' => ['q' => $search],
            'profiles' => $profiles,
            'comments' => $comments,
            'communityMessages' => $communityMessages,
            'staffConversation' => [
                'id' => $staffConversation->id,
                'name' => $staffConversation->name,
                'messages' => $staffMessages,
                'members' => $staffConversation->lifers
                    ->map(fn (Lifer $lifer) => $this->liferIdentity($lifer))
                    ->values(),
            ],
            'recentActions' => AdminAuditLog::query()
                ->where('action', 'like', 'moderation.%')
                ->with('actor.activeLifer:id,user_id,first_name,last_name')
                ->latest()
                ->limit(15)
                ->get()
                ->map(fn (AdminAuditLog $log) => $this->moderationActionPayload($log)),
        ]);
    }

    public function updateProfile(
        Request $request,
        Lifer $lifer,
        ProfileContentSanitizer $sanitizer,
    ): RedirectResponse {
        $validated = $request->validate([
            'content' => ['nullable', 'array'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $this->assertActive($lifer);
        $content = $sanitizer->sanitize($validated['content'] ?? null, $lifer->id);

        DB::transaction(function () use ($request, $lifer, $content, $validated): void {
            $profile = $lifer->profile()->lockForUpdate()->first();
            $before = $profile?->content;

            $profile = $lifer->profile()->updateOrCreate(
                ['lifer_id' => $lifer->id],
                ['content' => $content],
            );

            $referencedUrls = $this->imageSources($content);
            $removedImages = [];
            $lifer->profileImages()->get()->each(function (LiferImage $image) use ($referencedUrls, &$removedImages): void {
                if ($referencedUrls->contains('/storage/'.$image->image_path)) {
                    return;
                }

                $removedImages[] = [
                    'id' => $image->id,
                    'path' => $image->image_path,
                ];
                $image->delete();
            });

            $this->audit($request, 'moderation.profile.updated', $lifer, [
                'reason' => $validated['reason'],
                'before' => $before,
                'after' => $profile->content,
                'removed_images' => $removedImages,
            ]);
        });

        return back()->with('success', 'La présentation a été modérée et l’intervention enregistrée.');
    }

    public function destroyProfileImage(Request $request, LiferImage $image): RedirectResponse
    {
        $validated = $this->validateReason($request);
        $lifer = $image->lifer()->with('profile')->firstOrFail();
        $this->assertActive($lifer);

        DB::transaction(function () use ($request, $image, $lifer, $validated): void {
            $url = '/storage/'.$image->image_path;
            if ($lifer->profile) {
                $lifer->profile->update([
                    'content' => $this->withoutImage($lifer->profile->content, $url),
                ]);
            }

            $this->audit($request, 'moderation.profile-image.deleted', $lifer, [
                'reason' => $validated['reason'],
                'image_id' => $image->id,
                'image_path' => $image->image_path,
            ]);
            $image->delete();
        });

        return back()->with('success', 'L’image a été retirée du profil.');
    }

    public function destroyComment(Request $request, ProfileComment $comment): RedirectResponse
    {
        $validated = $this->validateReason($request);
        $comment->load(['author', 'receiver']);

        DB::transaction(function () use ($request, $comment, $validated): void {
            $this->audit($request, 'moderation.profile-comment.deleted', $comment->receiver, [
                'reason' => $validated['reason'],
                'comment_id' => $comment->id,
                'author_lifer_id' => $comment->author_lifer_id,
                'author_name' => "{$comment->author->first_name} {$comment->author->last_name}",
                'content' => $comment->content,
            ]);
            $comment->delete();
        });

        return back()->with('success', 'Le commentaire a été supprimé par la modération.');
    }

    public function destroyMessage(Request $request, Message $message): RedirectResponse
    {
        $validated = $this->validateReason($request);
        $message->load(['sender', 'conversation']);
        abort_unless($message->conversation?->type === Conversation::TYPE_GENERAL, 404);
        $conversationId = $message->conversation_id;
        $messageId = $message->id;

        DB::transaction(function () use ($request, $message, $validated): void {
            $this->audit($request, 'moderation.message.deleted', $message->sender, [
                'reason' => $validated['reason'],
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'conversation_name' => $this->conversationName($message->conversation),
                'sender_name' => "{$message->sender->first_name} {$message->sender->last_name}",
                'content' => $message->content,
            ]);
            $message->delete();
        });

        broadcast(new MessageDeleted($conversationId, $messageId))->toOthers();

        return back()->with('success', 'Le message a été supprimé par la modération.');
    }

    private function synchronizeStaffConversation(): Conversation
    {
        $conversation = Conversation::query()->firstOrCreate(
            ['key' => Conversation::KEY_STAFF],
            ['name' => 'Équipe Lifers', 'type' => Conversation::TYPE_GROUP],
        );
        $staffLiferIds = Lifer::query()
            ->active()
            ->whereHas('gameState')
            ->whereHas('user', function ($query): void {
                $query->where(function ($query): void {
                    $query
                        ->where('email', User::TRUSTED_ADMIN_EMAIL)
                        ->orWhereHas('roles', fn ($query) => $query->whereIn('name', [
                            Role::ADMIN,
                            Role::MODERATOR,
                        ]));
                });
            })
            ->pluck('id');

        $conversation->lifers()->sync($staffLiferIds);

        return $conversation->load('lifers.user.roles');
    }

    /** @return array{id: int, name: string, staff_role: ?string} */
    private function liferIdentity(Lifer $lifer): array
    {
        return [
            'id' => $lifer->id,
            'name' => "{$lifer->first_name} {$lifer->last_name}",
            'staff_role' => $lifer->staffRole(),
        ];
    }

    private function conversationName(Conversation $conversation): string
    {
        return match ($conversation->type) {
            Conversation::TYPE_GENERAL => 'Général',
            Conversation::TYPE_PRIVATE => 'Conversation privée',
            default => $conversation->name ?: 'Groupe sans nom',
        };
    }

    /** @return array<string, mixed> */
    private function moderationActionPayload(AdminAuditLog $log): array
    {
        $context = is_array($log->context) ? $log->context : [];
        $details = match ($log->action) {
            'moderation.profile.updated' => [
                'label' => 'Présentation de profil modifiée',
                'before_text' => $this->richText($context['before'] ?? null),
                'after_text' => $this->richText($context['after'] ?? null),
                'removed_images' => $context['removed_images']
                    ?? collect($context['removed_image_ids'] ?? [])
                        ->map(fn ($id) => ['id' => $id, 'path' => null])
                        ->all(),
            ],
            'moderation.profile-image.deleted' => [
                'label' => 'Image de présentation supprimée',
                'removed_text' => isset($context['image_path'])
                    ? basename((string) $context['image_path'])
                    : 'Image n°'.($context['image_id'] ?? 'inconnue'),
            ],
            'moderation.profile-comment.deleted' => [
                'label' => 'Commentaire de profil supprimé',
                'removed_text' => $context['content'] ?? null,
                'source' => $context['author_name'] ?? null,
            ],
            'moderation.message.deleted' => [
                'label' => 'Message du salon général supprimé',
                'removed_text' => $context['content'] ?? null,
                'source' => $context['sender_name'] ?? null,
            ],
            default => [
                'label' => 'Intervention de modération',
            ],
        };

        return [
            'id' => $log->id,
            'action' => $log->action,
            'actor' => $log->actor?->activeLifer
                ? "{$log->actor->activeLifer->first_name} {$log->actor->activeLifer->last_name}"
                : 'Membre de l’équipe',
            'target' => $context['lifer_name'] ?? null,
            'reason' => $context['reason'] ?? null,
            'created_at' => $log->created_at?->toIso8601String(),
            ...$details,
        ];
    }

    /** @param array<string, mixed>|null $content */
    private function richText(?array $content): ?string
    {
        if (! $content) {
            return null;
        }

        $parts = [];
        $walk = function (array $node) use (&$walk, &$parts): void {
            if (is_string($node['text'] ?? null)) {
                $parts[] = $node['text'];
            }

            foreach ($node['content'] ?? [] as $child) {
                if (is_array($child)) {
                    $walk($child);
                }
            }

            if (in_array($node['type'] ?? null, ['paragraph', 'heading', 'blockquote', 'listItem'], true)) {
                $parts[] = "\n";
            }
        };
        $walk($content);

        $text = trim(preg_replace('/[ \t]*\n[ \t]*/', "\n", implode('', $parts)) ?? '');

        return $text !== '' ? $text : null;
    }

    private function assertActive(Lifer $lifer): void
    {
        abort_unless($lifer->status === Lifer::STATUS_ACTIVE && $lifer->gameState()->exists(), 404);
    }

    /** @return array{reason: string} */
    private function validateReason(Request $request): array
    {
        return $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
    }

    /** @param array<string, mixed>|null $content */
    private function imageSources(?array $content): Collection
    {
        if (! $content) {
            return collect();
        }

        $sources = collect();
        $walk = function (array $node) use (&$walk, $sources): void {
            if (($node['type'] ?? null) === 'image' && is_string($node['attrs']['src'] ?? null)) {
                $sources->push($node['attrs']['src']);
            }

            foreach ($node['content'] ?? [] as $child) {
                if (is_array($child)) {
                    $walk($child);
                }
            }
        };
        $walk($content);

        return $sources->unique()->values();
    }

    /** @param array<string, mixed>|null $content */
    private function withoutImage(?array $content, string $url): ?array
    {
        if (! $content) {
            return $content;
        }

        $filter = function (array $node) use (&$filter, $url): ?array {
            if (($node['type'] ?? null) === 'image' && ($node['attrs']['src'] ?? null) === $url) {
                return null;
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $node['content'] = collect($node['content'])
                    ->filter(fn ($child) => is_array($child))
                    ->map(fn (array $child) => $filter($child))
                    ->filter()
                    ->values()
                    ->all();
            }

            return $node;
        };

        return $filter($content);
    }

    /** @param array<string, mixed> $context */
    private function audit(Request $request, string $action, Lifer $target, array $context): void
    {
        AdminAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'target_user_id' => $target->user_id,
            'action' => $action,
            'context' => [
                'lifer_id' => $target->id,
                'lifer_name' => "{$target->first_name} {$target->last_name}",
                ...$context,
            ],
        ]);
    }
}
