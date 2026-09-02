<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_account_can_log_in_and_visit_populated_game_pages(): void
    {
        $this->seed(DemoSeeder::class);

        $email = env('LIFERS_DEMO_EMAIL');
        $password = env('LIFERS_DEMO_PASSWORD');
        $user = User::where('email', $email)->firstOrFail();
        $lifer = $user->activeLifer()->firstOrFail();

        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertDatabaseHas('lifer_game_states', ['lifer_id' => $lifer->id]);
        $this->assertDatabaseHas('lifer_employments', ['lifer_id' => $lifer->id]);
        $this->assertDatabaseHas('lifer_study_enrollments', ['lifer_id' => $lifer->id, 'status' => 'active']);
        $this->assertDatabaseHas('inventory_items', ['inventory_id' => $lifer->id]);
        $this->assertDatabaseHas('lifer_sicknesses', ['lifer_id' => $lifer->id]);
        $this->assertDatabaseHas('lifer_profiles', ['lifer_id' => $lifer->id]);

        $this->post('/login', ['email' => $email, 'password' => $password])
            ->assertRedirect('/dashboard');

        foreach ([
            route('dashboard'),
            route('athome'),
            route('job'),
            route('study.index'),
            route('family.index'),
            route('city'),
            route('city.orphanage'),
            route('city.lifemarket'),
            route('city.entertainment'),
            route('doctor.index'),
            route('city.sport'),
            route('profil'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $general = Conversation::where('key', 'general')->firstOrFail();
        $this->get(route('social', ['id' => $general->id]))->assertOk();
    }
}
