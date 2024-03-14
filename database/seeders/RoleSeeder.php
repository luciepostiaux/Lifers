<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Role::updateOrCreate(['name' => 'admin'], [
            'name' => 'admin'
        ]);

        Role::updateOrCreate(['name' => 'user'], [
            'name' => 'user'
        ]);
    }
}
