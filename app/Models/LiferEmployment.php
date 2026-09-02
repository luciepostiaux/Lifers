<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class LiferEmployment extends Model
{
    public const DAYS_PER_SENIORITY_YEAR = 3;

    public const ANNUAL_RAISE_RATE = 0.02;

    public const MAX_RAISES = 10;

    protected $primaryKey = 'lifer_id';

    public $incrementing = false;

    protected $fillable = ['lifer_id', 'job_id', 'started_at', 'last_salary_paid_on'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_salary_paid_on' => 'date',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function seniorityYears(?CarbonInterface $at = null): int
    {
        $at ??= now();

        if (! $this->started_at || $this->started_at->greaterThan($at)) {
            return 0;
        }

        $elapsedDays = (int) floor($this->started_at->diffInDays($at, true));

        return intdiv($elapsedDays, self::DAYS_PER_SENIORITY_YEAR);
    }

    public function raiseCount(?CarbonInterface $at = null): int
    {
        return min(self::MAX_RAISES, $this->seniorityYears($at));
    }

    public function currentSalary(?CarbonInterface $at = null): float
    {
        $baseSalary = (float) $this->job->salary;

        return round(
            $baseSalary * ((1 + self::ANNUAL_RAISE_RATE) ** $this->raiseCount($at)),
            2,
        );
    }

    public function nextRaiseAt(?CarbonInterface $at = null): ?CarbonInterface
    {
        $raiseCount = $this->raiseCount($at);

        if ($raiseCount >= self::MAX_RAISES) {
            return null;
        }

        return $this->started_at
            ->copy()
            ->addDays(($raiseCount + 1) * self::DAYS_PER_SENIORITY_YEAR);
    }
}
