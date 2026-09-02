<?php

namespace App\Services;

use App\Models\AccountBan;
use App\Models\AccountBanIpAddress;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountBanService
{
    public function ban(
        User $actor,
        string $email,
        string $reason,
        bool $blockKnownIpAddresses = false,
    ): AccountBan {
        $email = Str::lower(trim($email));
        $reason = trim($reason);

        if ($email === User::TRUSTED_ADMIN_EMAIL) {
            throw ValidationException::withMessages([
                'email' => 'Le compte administrateur principal ne peut pas être banni.',
            ]);
        }

        return DB::transaction(function () use ($actor, $email, $reason, $blockKnownIpAddresses): AccountBan {
            $user = User::query()->where('email', $email)->lockForUpdate()->first();
            $knownIpAddresses = $user && $blockKnownIpAddresses
                ? DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->whereNotNull('ip_address')
                    ->distinct()
                    ->pluck('ip_address')
                    ->filter(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP))
                    ->values()
                : collect();

            $ban = AccountBan::query()->lockForUpdate()->firstOrNew(['email' => $email]);
            $ban->fill([
                'user_id' => $user?->id,
                'reason' => $reason,
                'banned_by_user_id' => $actor->id,
                'banned_at' => now(),
                'revoked_at' => null,
                'revoked_by_user_id' => null,
                'revocation_reason' => null,
            ])->save();

            $ban->ipAddresses()->delete();
            foreach ($knownIpAddresses as $ipAddress) {
                AccountBanIpAddress::query()->updateOrCreate(
                    ['ip_hash' => $this->hashIpAddress($ipAddress)],
                    [
                        'account_ban_id' => $ban->id,
                        'masked_ip' => $this->maskIpAddress($ipAddress),
                    ],
                );
            }

            if ($user) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
                $user->tokens()->delete();
                $user->forceFill(['is_online' => false])->save();
            }

            AdminAuditLog::query()->create([
                'actor_user_id' => $actor->id,
                'target_user_id' => $user?->id,
                'action' => 'account.banned',
                'context' => [
                    'email' => $email,
                    'reason' => $reason,
                    'blocked_ip_count' => $knownIpAddresses->count(),
                ],
            ]);

            return $ban->fresh(['user:id,name,email', 'ipAddresses']);
        });
    }

    public function revoke(User $actor, AccountBan $ban, string $reason): AccountBan
    {
        return DB::transaction(function () use ($actor, $ban, $reason): AccountBan {
            $lockedBan = AccountBan::query()->lockForUpdate()->findOrFail($ban->id);

            if ($lockedBan->revoked_at) {
                throw ValidationException::withMessages([
                    'ban' => 'Ce bannissement est déjà levé.',
                ]);
            }

            $blockedIpCount = $lockedBan->ipAddresses()->count();
            $lockedBan->update([
                'revoked_at' => now(),
                'revoked_by_user_id' => $actor->id,
                'revocation_reason' => trim($reason),
            ]);
            $lockedBan->ipAddresses()->delete();

            AdminAuditLog::query()->create([
                'actor_user_id' => $actor->id,
                'target_user_id' => $lockedBan->user_id,
                'action' => 'account.unbanned',
                'context' => [
                    'email' => $lockedBan->email,
                    'reason' => trim($reason),
                    'released_ip_count' => $blockedIpCount,
                ],
            ]);

            return $lockedBan->fresh();
        });
    }

    public function isEmailBanned(?string $email): bool
    {
        if (blank($email)) {
            return false;
        }

        return AccountBan::query()
            ->active()
            ->where('email', Str::lower(trim($email)))
            ->exists();
    }

    public function isIpAddressBanned(?string $ipAddress): bool
    {
        if (! $ipAddress || ! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return false;
        }

        return AccountBanIpAddress::query()
            ->where('ip_hash', $this->hashIpAddress($ipAddress))
            ->whereHas('accountBan', fn ($query) => $query->active())
            ->exists();
    }

    private function hashIpAddress(string $ipAddress): string
    {
        return hash_hmac('sha256', $ipAddress, (string) config('app.key'));
    }

    private function maskIpAddress(string $ipAddress): string
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ipAddress);

            return "{$parts[0]}.{$parts[1]}.x.x";
        }

        $parts = array_slice(explode(':', $ipAddress), 0, 3);

        return implode(':', $parts).'::/48';
    }
}
