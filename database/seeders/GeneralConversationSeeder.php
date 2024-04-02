<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralConversationSeeder extends Seeder
{
    public function run()
    {
        $conversation = Conversation::firstOrCreate([
            'name' => 'Général',
        ]);

        $users = User::all();

        foreach ($users as $user) {
            $conversation->users()->syncWithoutDetaching($user->id);
        }
    }
}
