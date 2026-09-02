<?php

namespace Tests\Concerns;

use App\Models\Lifer;
use App\Models\User;

trait CreatesLifers
{
    protected function createUserWithLifer(
        int $money = 900,
        array $gauges = [],
        string $firstName = 'Test',
        string $lastName = 'Lifer',
    ): array {
        $user = User::factory()->create();
        $lifer = Lifer::factory()->for($user)->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        $lifer->gameState()->update(['money' => $money]);

        if ($gauges !== []) {
            $lifer->lifeGauge()->update($gauges);
        }

        return [$user, $lifer->fresh(['gameState', 'lifeGauge', 'inventory'])];
    }
}
