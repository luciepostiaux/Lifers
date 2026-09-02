<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\Job;
use App\Models\LiferEmployment;
use App\Models\LiferStudyEnrollment;
use App\Models\Place;
use App\Models\Sickness;
use App\Models\Study;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class GameIntegrityTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_resigning_from_a_job_removes_employment_without_copying_salary_to_lifer(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $job = $this->createJob('Métier actuel', 120);
        LiferEmployment::create(['lifer_id' => $lifer->id, 'job_id' => $job->id, 'started_at' => now()]);

        $this->actingAs($user)->post(route('job.resign'))->assertRedirect(route('job'));

        $this->assertDatabaseMissing('lifer_employments', ['lifer_id' => $lifer->id]);
        $this->assertFalse(array_key_exists('salary', $lifer->getAttributes()));
    }

    public function test_changing_job_requires_the_new_jobs_diploma(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $job = $this->createJob('Nouveau métier', 240, true);

        $this->actingAs($user)
            ->from(route('job'))
            ->post(route('job.change', $job))
            ->assertSessionHasErrors('job');

        $lifer->diplomas()->attach($job->required_diploma_id, ['earned_at' => now()]);

        $this->actingAs($user)
            ->post(route('job.change', $job))
            ->assertRedirect(route('job'));

        $this->assertDatabaseHas('lifer_employments', ['lifer_id' => $lifer->id, 'job_id' => $job->id]);
    }

    public function test_changing_job_resets_seniority_and_uses_the_new_base_salary(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $oldJob = $this->createJob('Ancien métier', 100);
        $newJob = $this->createJob('Nouveau métier sans ancienneté', 160);
        LiferEmployment::create([
            'lifer_id' => $lifer->id,
            'job_id' => $oldJob->id,
            'started_at' => now()->subDays(30),
        ]);

        $this->actingAs($user)
            ->post(route('job.apply', $newJob))
            ->assertRedirect();

        $employment = $lifer->employment()->with('job')->firstOrFail();

        $this->assertSame($newJob->id, $employment->job_id);
        $this->assertSame(0, $employment->seniorityYears());
        $this->assertSame(0, $employment->raiseCount());
        $this->assertSame(160.0, $employment->currentSalary());
    }

    public function test_another_job_or_study_cannot_be_presented_as_current(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        $currentJob = $this->createJob('Métier actuel', 100);
        $otherJob = $this->createJob('Autre métier', 200);
        LiferEmployment::create(['lifer_id' => $lifer->id, 'job_id' => $currentJob->id, 'started_at' => now()]);

        $currentStudy = $this->createStudy('Étude actuelle');
        $otherStudy = $this->createStudy('Autre étude');
        LiferStudyEnrollment::create([
            'lifer_id' => $lifer->id,
            'study_id' => $currentStudy->id,
            'started_at' => now(),
            'ends_at' => now()->addDay(),
            'status' => LiferStudyEnrollment::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)->get(route('job.current.show', $otherJob))->assertNotFound();
        $this->actingAs($user)->get(route('study.current.show', $otherStudy))->assertNotFound();
    }

    public function test_treating_sickness_is_owned_scoped_and_atomic(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(500);
        $sickness = $this->createSickness(75);

        $this->actingAs($user)
            ->from(route('doctor.index'))
            ->post(route('treat-sickness'), ['sicknessId' => $sickness->id])
            ->assertSessionHasErrors('sicknessId');
        $this->assertSame('500.00', $lifer->gameState->fresh()->money);

        $lifer->sicknesses()->attach($sickness->id, ['contracted_at' => now()]);
        $this->actingAs($user)
            ->from(route('doctor.index'))
            ->post(route('treat-sickness'), ['sicknessId' => $sickness->id])
            ->assertRedirect(route('doctor.index'));

        $this->assertSame('425.00', $lifer->gameState->fresh()->money);
        $this->assertDatabaseMissing('lifer_sicknesses', ['lifer_id' => $lifer->id, 'sickness_id' => $sickness->id]);
    }

    public function test_life_gauges_page_renders_the_active_lifers_values(): void
    {
        [$user] = $this->createUserWithLifer(900, ['health' => 80]);

        $this->actingAs($user)
            ->get(route('life-gauges.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('LifeGauges/Index')
                ->where('lifeGauges.Santé', 80));
    }

    private function createJob(string $name, int $salary, bool $requiresDiploma = false): Job
    {
        $diploma = Diploma::create(['name' => 'Diplôme '.$name, 'description' => 'Diplôme de test']);

        return Job::create([
            'name' => $name,
            'short_description' => 'Description courte',
            'long_description' => 'Description longue',
            'salary' => $salary,
            'required_diploma_id' => $requiresDiploma ? $diploma->id : null,
        ]);
    }

    private function createStudy(string $name): Study
    {
        $diploma = Diploma::create(['name' => 'Diplôme '.$name, 'description' => 'Diplôme de test']);
        $place = Place::firstOrCreate(['name' => 'Université']);

        return Study::create([
            'name' => $name,
            'short_description' => 'Description courte',
            'long_description' => 'Description longue',
            'price' => 100,
            'duration_days' => 3,
            'awarded_diploma_id' => $diploma->id,
            'place_id' => $place->id,
        ]);
    }

    private function createSickness(int $treatmentCost): Sickness
    {
        return Sickness::create([
            'name' => 'Maladie de test',
            'description' => 'Description de test',
            'type' => 'random',
            'self_resolving' => true,
            'treatment_cost' => $treatmentCost,
        ]);
    }
}
