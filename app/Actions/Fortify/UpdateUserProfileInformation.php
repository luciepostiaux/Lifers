<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\AccountBanService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(private readonly AccountBanService $bans) {}

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                $user->isTrustedAdmin()
                    ? Rule::in([User::TRUSTED_ADMIN_EMAIL])
                    : Rule::notIn([User::TRUSTED_ADMIN_EMAIL]),
                Rule::unique('users')->ignore($user->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->bans->isEmailBanned((string) $value)) {
                        $fail('Cette adresse ne peut pas être utilisée.');
                    }
                },
            ],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ], [
            'email.in' => 'L’adresse du compte administrateur principal ne peut pas être modifiée.',
            'email.not_in' => 'Cette adresse est réservée au compte administrateur de Lifers.',
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
