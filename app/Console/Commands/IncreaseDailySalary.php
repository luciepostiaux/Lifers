<?php

namespace App\Console\Commands;

use App\Models\LiferEmployment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IncreaseDailySalary extends Command
{
    protected $signature = 'increase:daily-salary';

    protected $description = 'Verse quotidiennement le salaire des Lifers employés';

    public function handle(): int
    {
        LiferEmployment::query()
            ->where(function ($query) {
                $query->whereNull('last_salary_paid_on')
                    ->orWhereDate('last_salary_paid_on', '<', today());
            })
            ->with('job')
            ->each(function (LiferEmployment $employment) {
                DB::transaction(function () use ($employment) {
                    $locked = LiferEmployment::query()->lockForUpdate()->findOrFail($employment->lifer_id);

                    if ($locked->last_salary_paid_on?->isToday()) {
                        return;
                    }

                    $locked->lifer()->firstOrFail()->gameState()->lockForUpdate()->firstOrFail()
                        ->increment('money', $locked->currentSalary());
                    $locked->update(['last_salary_paid_on' => today()]);
                });
            });

        $this->info('Les salaires quotidiens ont été distribués.');

        return self::SUCCESS;
    }
}
