<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    public const TRUSTED_ADMIN_EMAIL = 'admin@admin.com';

    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use MustVerifyEmailTrait {
        hasVerifiedEmail as protected hasVerifiedEmailFromTimestamp;
    }
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'adult_confirmed_at',
        'terms_accepted_at',
        'terms_version',
        'privacy_acknowledged_at',
        'privacy_version',
        'consentement_newsletter',
        'date_consentement',
        'consentement_rgpd',
        'last_login_at',
        'consecutive_login_days',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'adult_confirmed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_acknowledged_at' => 'datetime',
        'consentement_newsletter' => 'boolean',
        'date_consentement' => 'datetime',
        'consentement_rgpd' => 'boolean',
        'last_login_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function lifers()
    {
        return $this->hasMany(Lifer::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->isTrustedAdmin() || $this->hasVerifiedEmailFromTimestamp();
    }

    public function activeLifer()
    {
        return $this->hasOne(Lifer::class)->where('status', Lifer::STATUS_ACTIVE);
    }

    public function accountBan()
    {
        return $this->hasOne(AccountBan::class);
    }

    public function isBanned(): bool
    {
        return AccountBan::query()
            ->active()
            ->where('email', Str::lower(trim($this->email)))
            ->exists();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles()->where('name', $role)->exists();
    }

    public function isTrustedAdmin(): bool
    {
        return Str::lower(trim($this->email)) === self::TRUSTED_ADMIN_EMAIL;
    }

    public function isAdmin(): bool
    {
        return $this->isTrustedAdmin() || $this->hasRole(Role::ADMIN);
    }

    public function canModerate(): bool
    {
        return $this->isAdmin() || $this->hasRole(Role::MODERATOR);
    }

    public function displayRole(): string
    {
        if ($this->isAdmin()) {
            return Role::ADMIN;
        }

        if ($this->hasRole(Role::MODERATOR)) {
            return Role::MODERATOR;
        }

        return Role::USER;
    }
}
