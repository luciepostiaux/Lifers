<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\Job;
use App\Models\LiferEmployment;
use App\Models\Sickness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class ScheduledGameCommandsTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_salary_and_gauge_tick_are_applied_only_once_per_day(): void
    {
        [, $lifer] = $this->createUserWithLifer(100, [
            'hunger' => 100,
            'thirst' => 100,
            'clean' => 100,
            'happiness' => 100,
            'entertainment' => 100,
            'physical_condition' => 100,
            'health' => 100,
        ]);
        $diploma = Diploma::create(['name' => 'Test', 'description' => 'Test']);
        $job = Job::create([
            'name' => 'Métier test',
            'short_description' => 'Test',
            'salary' => 50,
            'required_diploma_id' => $diploma->id,
        ]);
        LiferEmployment::create(['lifer_id' => $lifer->id, 'job_id' => $job->id, 'started_at' => now()]);

        $this->artisan('increase:daily-salary')->assertSuccessful();
        $this->artisan('increase:daily-salary')->assertSuccessful();
        $this->artisan('decrease:life-gauges')->assertSuccessful();
        $this->artisan('decrease:life-gauges')->assertSuccessful();

        $this->assertSame('150.00', $lifer->gameState->fresh()->money);
        $this->assertSame(70, $lifer->lifeGauge->fresh()->hunger);
        $this->assertSame(65, $lifer->lifeGauge->fresh()->thirst);
        $this->assertSame(90, $lifer->lifeGauge->fresh()->health);
    }

    public function test_salary_increases_by_two_percent_every_three_days_and_stops_after_ten_raises(): void
    {
        [, $experiencedLifer] = $this->createUserWithLifer(100);
        [, $cappedLifer] = $this->createUserWithLifer(100);
        $job = Job::create([
            'name' => 'Métier avec ancienneté',
            'short_description' => 'Test',
            'salary' => 100,
        ]);

        $experiencedEmployment = LiferEmployment::create([
            'lifer_id' => $experiencedLifer->id,
            'job_id' => $job->id,
            'started_at' => now()->subDays(3),
        ]);
        $cappedEmployment = LiferEmployment::create([
            'lifer_id' => $cappedLifer->id,
            'job_id' => $job->id,
            'started_at' => now()->subDays(60),
        ]);

        $this->assertSame(1, $experiencedEmployment->seniorityYears());
        $this->assertSame(1, $experiencedEmployment->raiseCount());
        $this->assertSame(102.0, $experiencedEmployment->currentSalary());
        $this->assertSame(20, $cappedEmployment->seniorityYears());
        $this->assertSame(10, $cappedEmployment->raiseCount());
        $this->assertSame(121.9, $cappedEmployment->currentSalary());
        $this->assertNull($cappedEmployment->nextRaiseAt());

        $this->artisan('increase:daily-salary')->assertSuccessful();

        $this->assertSame('202.00', $experiencedLifer->gameState->fresh()->money);
        $this->assertSame('221.90', $cappedLifer->gameState->fresh()->money);
    }

    public function test_expired_self_resolving_sickness_is_removed_and_can_recur(): void
    {
        [, $lifer] = $this->createUserWithLifer();
        $sickness = Sickness::create([
            'name' => 'Rhume récurrent',
            'type' => 'random',
            'duration_days' => 2,
            'self_resolving' => true,
        ]);
        $lifer->sicknesses()->attach($sickness->id, [
            'contracted_at' => now()->subDays(3),
            'expected_recovery_at' => now()->subDay(),
        ]);

        $this->artisan('resolve:expired-sicknesses')->assertSuccessful();
        $this->assertDatabaseMissing('lifer_sicknesses', [
            'lifer_id' => $lifer->id,
            'sickness_id' => $sickness->id,
        ]);

        $lifer->sicknesses()->attach($sickness->id, ['contracted_at' => now()]);
        $this->assertDatabaseHas('lifer_sicknesses', [
            'lifer_id' => $lifer->id,
            'sickness_id' => $sickness->id,
        ]);
    }

    public function test_non_self_resolving_sickness_remains_after_due_date(): void
    {
        [, $lifer] = $this->createUserWithLifer();
        $sickness = Sickness::create([
            'name' => 'Maladie nécessitant un soin',
            'type' => 'severe',
            'duration_days' => 2,
            'self_resolving' => false,
            'needs_doctor' => true,
        ]);
        $lifer->sicknesses()->attach($sickness->id, [
            'contracted_at' => now()->subDays(3),
            'expected_recovery_at' => now()->subDay(),
        ]);

        $this->artisan('resolve:expired-sicknesses')->assertSuccessful();

        $this->assertDatabaseHas('lifer_sicknesses', [
            'lifer_id' => $lifer->id,
            'sickness_id' => $sickness->id,
        ]);
    }
}
