<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {

        User::create([
            'id' => '1',
            'pseudo' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'created_at' => now()->toDateString(),
            'consentement_newsletter' => true,
            'date_consentement' => now(),
            'consentement_rgpd' => true,
            'last_login_at' => now(),
            'consecutive_login_days' => 1,
        ]);
        // User::create([
        //     'id' => '2',
        //     'pseudo' => 'test',
        //     'email' => 'test2@example.com',
        //     'password' => Hash::make('password'),
        //     'created_at' => now()->toDateString(),
        //     'consentement_newsletter' => true,
        //     'date_consentement' => now(),
        //     'consentement_rgpd' => true,
        //     'last_login_at' => now(),
        //     'consecutive_login_days' => 1,
        // ]);
        User::factory()->count(25)->create();
    }
}
