<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Assurez-vous que les rôles existent
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Créer l'utilisateur administrateur spécifique
        $adminUser = User::updateOrCreate([
            'email' => 'admin@example.com'
        ], [
            'id' => 1,
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
            'created_at' => now()->toDateString(),
            'consentement_newsletter' => true,
            'date_consentement' => now(),
            'consentement_rgpd' => true,
            'last_login_at' => now(),
            'consecutive_login_days' => 1,
        ]);
        $adminUser->roles()->sync([$adminRole->id]);


        $testUser = User::create([
            'id' => 2,
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'created_at' => now()->toDateString(),
            'consentement_newsletter' => true,
            'date_consentement' => now(),
            'consentement_rgpd' => true,
            'last_login_at' => now(),
            'consecutive_login_days' => 1,
        ]);
        $testUser->roles()->attach($userRole->id);


        // User::factory()->count(25)->create()->each(function ($user) use ($userRole) {
        //     $user->roles()->attach($userRole->id);
        // });
    }
}
