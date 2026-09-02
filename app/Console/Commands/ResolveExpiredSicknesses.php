<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResolveExpiredSicknesses extends Command
{
    protected $signature = 'resolve:expired-sicknesses';

    protected $description = 'Guérit les maladies spontanément résolues arrivées à échéance';

    public function handle(): int
    {
        DB::table('lifer_sicknesses')
            ->join('sicknesses', 'sicknesses.id', '=', 'lifer_sicknesses.sickness_id')
            ->where('sicknesses.self_resolving', true)
            ->whereNotNull('lifer_sicknesses.expected_recovery_at')
            ->where('lifer_sicknesses.expected_recovery_at', '<=', now())
            ->delete();

        return self::SUCCESS;
    }
}
