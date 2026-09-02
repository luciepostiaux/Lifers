<?php

namespace App\Services;

use App\Models\Lifer;

class FamilyActionRandomizer
{
    public const CONCEPTION_CHANCE_PERCENT = 25;

    public function conceptionSucceeds(): bool
    {
        return random_int(1, 100) <= self::CONCEPTION_CHANCE_PERCENT;
    }

    public function childrenCount(): int
    {
        $roll = random_int(1, 100);

        if ($roll <= 85) {
            return 1;
        }

        if ($roll <= 98) {
            return 2;
        }

        return 3;
    }

    public function childSex(): string
    {
        return random_int(0, 1) === 0 ? Lifer::SEX_FEMALE : Lifer::SEX_MALE;
    }
}
