<?php

namespace App\Actions\Fortify;

use App\Models\Role;
use App\Models\User;
use App\Services\AccountBanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly AccountBanService $bans) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $email = Str::lower(trim((string) ($input['email'] ?? '')));

        Validator::make([...$input, 'email' => $email], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::notIn([User::TRUSTED_ADMIN_EMAIL]),
                Rule::unique('users', 'email'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->bans->isEmailBanned((string) $value)) {
                        $fail('Cette adresse ne peut pas être utilisée.');
                    }
                },
            ],
            'password' => $this->passwordRules(),
            'adult_confirmation' => ['accepted', 'required'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'email.not_in' => 'Cette adresse est réservée au compte administrateur de Lifers.',
            'adult_confirmation.accepted' => 'Tu dois confirmer avoir 18 ans ou plus pour créer un compte.',
            'adult_confirmation.required' => 'Tu dois confirmer avoir 18 ans ou plus pour créer un compte.',
            'terms.accepted' => 'Tu dois accepter les conditions d’utilisation et prendre connaissance de la politique de confidentialité.',
            'terms.required' => 'Tu dois accepter les conditions d’utilisation et prendre connaissance de la politique de confidentialité.',
        ])->validate();

        return DB::transaction(function () use ($email, $input): User {
            $acceptedAt = now();
            $user = User::create([
                'name' => $input['name'],
                'email' => $email,
                'password' => Hash::make($input['password']),
                'adult_confirmed_at' => $acceptedAt,
                'terms_accepted_at' => $acceptedAt,
                'terms_version' => config('legal.terms_version'),
                'privacy_acknowledged_at' => $acceptedAt,
                'privacy_version' => config('legal.privacy_version'),
            ]);

            $userRole = Role::query()->firstOrCreate(['name' => Role::USER]);
            $user->roles()->syncWithoutDetaching($userRole->id);

            return $user;
        });
    }
}
