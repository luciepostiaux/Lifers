<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunSharedHostingTick extends Command
{
    protected $signature = 'lifers:shared-hosting-tick';

    protected $description = 'Exécute les cycles idempotents de Lifers depuis une tâche horaire mutualisée';

    public function handle(): int
    {
        foreach (['lifers:daily-tick', 'advance:family-lifecycle'] as $command) {
            if ($this->call($command) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        $this->components->info('Les cycles compatibles avec l’hébergement mutualisé ont été exécutés.');

        return self::SUCCESS;
    }
}
