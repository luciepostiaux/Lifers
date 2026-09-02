<?php

namespace App\Services;

class GameRandomizer
{
    public function succeeds(float $percentage): bool
    {
        $boundedPercentage = max(0, min(100, $percentage));

        return random_int(1, 1_000_000) <= (int) round($boundedPercentage * 10_000);
    }
}
