<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\AccountBan;
use App\Models\Diploma;
use App\Models\Lifer;
use App\Models\ProfileComment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $search = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $roleFilter = in_array($request->query('role'), [Role::USER, Role::MODERATOR, Role::ADMIN], true)
            ? $request->query('role')
            : 'all';
        $adminLifer = $request->user()->activeLifer()->with('gameState')->first();

        $users = User::query()
            ->with([
                'roles:id,name',
                'activeLifer:id,user_id,first_name,last_name,status',
                'accountBan:id,user_id,email,revoked_at',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('activeLifer', function ($query) use ($search): void {
                            $query
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($roleFilter === Role::ADMIN, function ($query): void {
                $query->where(function ($query): void {
                    $query
                        ->where('email', User::TRUSTED_ADMIN_EMAIL)
                        ->orWhereHas('roles', fn ($query) => $query->where('name', Role::ADMIN));
                });
            })
            ->when($roleFilter === Role::MODERATOR, function ($query): void {
                $query
                    ->where('email', '!=', User::TRUSTED_ADMIN_EMAIL)
                    ->whereHas('roles', fn ($query) => $query->where('name', Role::MODERATOR));
            })
            ->when($roleFilter === Role::USER, function ($query): void {
                $query
                    ->where('email', '!=', User::TRUSTED_ADMIN_EMAIL)
                    ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', [
                        Role::ADMIN,
                        Role::MODERATOR,
                    ]));
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $users->getCollection()->transform(fn (User $user) => [
            'id' => $user->id,
            'account_name' => $user->name,
            'email' => $user->email,
            'lifer_name' => $user->activeLifer
                ? "{$user->activeLifer->first_name} {$user->activeLifer->last_name}"
                : null,
            'role' => $user->displayRole(),
            'is_protected' => $user->isTrustedAdmin(),
            'is_banned' => $user->accountBan?->revoked_at === null && $user->accountBan !== null,
            'active_lifer_id' => $user->activeLifer?->id,
            'created_at' => $user->created_at?->toIso8601String(),
        ]);

        $lifers = Lifer::query()
            ->active()
            ->with('diplomas:id,name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'user_id', 'first_name', 'last_name'])
            ->map(fn (Lifer $lifer) => [
                'id' => $lifer->id,
                'name' => "{$lifer->first_name} {$lifer->last_name}",
                'diploma_ids' => $lifer->diplomas->pluck('id')->values(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'lifer' => $adminLifer ? [
                'id' => $adminLifer->id,
                'first_name' => $adminLifer->first_name,
                'last_name' => $adminLifer->last_name,
            ] : null,
            'money' => $adminLifer?->gameState?->money,
            'stats' => [
                'users' => User::query()->count(),
                'moderators' => User::query()
                    ->whereHas('roles', fn ($query) => $query->where('name', Role::MODERATOR))
                    ->count(),
                'active_lifers' => Lifer::query()->active()->count(),
                'pending_comments' => ProfileComment::query()
                    ->where('status', ProfileComment::STATUS_PENDING)
                    ->count(),
                'banned_accounts' => AccountBan::query()->active()->count(),
            ],
            'users' => $users,
            'filters' => [
                'q' => $search,
                'role' => $roleFilter,
            ],
            'lifers' => $lifers,
            'diplomas' => Diploma::query()->orderBy('name')->get(['id', 'name']),
            'bans' => AccountBan::query()
                ->active()
                ->with([
                    'user:id,name,email',
                    'bannedBy:id,name',
                    'ipAddresses:id,account_ban_id,masked_ip',
                ])
                ->latest('banned_at')
                ->get()
                ->map(fn (AccountBan $ban) => [
                    'id' => $ban->id,
                    'email' => $ban->email,
                    'account_name' => $ban->user?->name,
                    'reason' => $ban->reason,
                    'banned_by' => $ban->bannedBy?->name ?? 'Compte supprimé',
                    'banned_at' => $ban->banned_at?->toIso8601String(),
                    'masked_ip_addresses' => $ban->ipAddresses->pluck('masked_ip')->values(),
                ]),
            'auditLogs' => AdminAuditLog::query()
                ->with(['actor:id,name,email', 'target:id,name,email'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (AdminAuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => $log->actor?->name ?? 'Compte supprimé',
                    'target' => $log->target?->name,
                    'context' => $log->context,
                    'created_at' => $log->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(Role::ASSIGNABLE)],
        ]);

        if ($user->isTrustedAdmin()) {
            throw ValidationException::withMessages([
                'role' => 'Le rôle du compte administrateur principal est protégé.',
            ]);
        }

        return DB::transaction(function () use ($request, $user, $validated): RedirectResponse {
            $target = User::query()->with('roles')->lockForUpdate()->findOrFail($user->id);
            $previousRole = $target->displayRole();

            if ($previousRole === $validated['role']) {
                return back()->with('warning', 'Ce compte possède déjà ce rôle.');
            }

            $role = Role::query()->firstOrCreate(['name' => $validated['role']]);
            $target->roles()->sync([$role->id]);

            $this->audit($request->user(), 'role.updated', $target, [
                'from' => $previousRole,
                'to' => $validated['role'],
            ]);

            return back()->with('success', 'Le rôle du compte a été mis à jour.');
        });
    }

    public function grantDiploma(Request $request): RedirectResponse
    {
        $validated = $this->validateDiplomaAction($request);

        return DB::transaction(function () use ($request, $validated): RedirectResponse {
            $lifer = Lifer::query()
                ->active()
                ->with('user:id,name,email')
                ->lockForUpdate()
                ->findOrFail($validated['liferId']);
            $diploma = Diploma::query()->findOrFail($validated['diplomaId']);

            if ($lifer->diplomas()->whereKey($diploma->id)->exists()) {
                return back()->with('error', 'Ce Lifer possède déjà ce diplôme.');
            }

            $lifer->diplomas()->attach($diploma->id, ['earned_at' => now()]);

            $this->audit($request->user(), 'diploma.granted', $lifer->user, [
                'lifer_id' => $lifer->id,
                'lifer_name' => "{$lifer->first_name} {$lifer->last_name}",
                'diploma_id' => $diploma->id,
                'diploma_name' => $diploma->name,
            ]);

            return back()->with('success', 'Diplôme attribué avec succès.');
        });
    }

    public function removeDiploma(Request $request): RedirectResponse
    {
        $validated = $this->validateDiplomaAction($request);

        return DB::transaction(function () use ($request, $validated): RedirectResponse {
            $lifer = Lifer::query()
                ->active()
                ->with('user:id,name,email')
                ->lockForUpdate()
                ->findOrFail($validated['liferId']);
            $diploma = Diploma::query()->findOrFail($validated['diplomaId']);
            $removed = $lifer->diplomas()->detach($diploma->id);

            if (! $removed) {
                return back()->with('warning', 'Ce Lifer ne possédait pas ce diplôme.');
            }

            $this->audit($request->user(), 'diploma.removed', $lifer->user, [
                'lifer_id' => $lifer->id,
                'lifer_name' => "{$lifer->first_name} {$lifer->last_name}",
                'diploma_id' => $diploma->id,
                'diploma_name' => $diploma->name,
            ]);

            return back()->with('success', 'Diplôme retiré avec succès.');
        });
    }

    /** @return array{liferId: int, diplomaId: int} */
    private function validateDiplomaAction(Request $request): array
    {
        return $request->validate([
            'liferId' => ['required', 'integer', 'exists:lifer_game_states,lifer_id'],
            'diplomaId' => ['required', 'integer', 'exists:diplomas,id'],
        ]);
    }

    /** @param array<string, mixed> $context */
    private function audit(User $actor, string $action, ?User $target, array $context = []): void
    {
        AdminAuditLog::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target?->id,
            'action' => $action,
            'context' => $context,
        ]);
    }
}
