<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Models\Job;
use App\Models\LiferEmployment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index()
    {
        $lifer = $this->activeLifer([
            'diplomas',
            'gameState',
            'employment.job.requiredDiploma',
            'employment.job.place',
        ]);

        return Inertia::render('Job/Index', [
            'jobs' => Job::with(['requiredDiploma', 'place'])->get(),
            'userDiplomas' => $lifer->diplomas,
            'currentJob' => $this->currentJobPayload($lifer->employment),
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function apply(ApplyJobRequest $request, int $jobId)
    {
        $this->assignJob($jobId);

        return back()->with('message', 'Vous avez postulé avec succès pour ce métier.');
    }

    public function showCurrentJob(int $jobId)
    {
        $lifer = $this->activeLifer([
            'gameState',
            'employment.job.requiredDiploma',
            'employment.job.place',
        ]);
        $employment = $lifer->employment;

        abort_unless($employment && $employment->job_id === $jobId, 404);

        return Inertia::render('Job/Current', [
            'jobDetails' => $employment->job,
            'employmentDetails' => $this->employmentPayload($employment),
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function resign()
    {
        $lifer = $this->activeLifer(['employment']);

        if (! $lifer->employment) {
            return back()->withErrors(['msg' => 'Vous n’avez actuellement aucun métier.']);
        }

        $lifer->employment->delete();

        return redirect()->route('job')->with('message', 'Vous avez démissionné avec succès.');
    }

    public function changeJob(Request $request, int $newJobId)
    {
        $this->assignJob($newJobId);

        return redirect()->route('job')->with('message', 'Changement de métier effectué avec succès.');
    }

    private function assignJob(int $jobId): void
    {
        $lifer = $this->activeLifer(['diplomas']);
        $job = Job::findOrFail($jobId);

        if ($job->required_diploma_id && ! $lifer->diplomas->contains('id', $job->required_diploma_id)) {
            throw ValidationException::withMessages([
                'job' => 'Tu dois avant tout obtenir le diplôme lié à ce métier.',
            ]);
        }

        DB::transaction(function () use ($lifer, $job) {
            LiferEmployment::query()->updateOrCreate(
                ['lifer_id' => $lifer->id],
                ['job_id' => $job->id, 'started_at' => now()],
            );
        });
    }

    private function currentJobPayload(?LiferEmployment $employment): ?array
    {
        if (! $employment) {
            return null;
        }

        return array_merge(
            $employment->job->toArray(),
            $this->employmentPayload($employment),
        );
    }

    private function employmentPayload(LiferEmployment $employment): array
    {
        return [
            'started_at' => $employment->started_at,
            'current_salary' => $employment->currentSalary(),
            'seniority_years' => $employment->seniorityYears(),
            'raise_count' => $employment->raiseCount(),
            'max_raises' => LiferEmployment::MAX_RAISES,
            'raise_rate' => LiferEmployment::ANNUAL_RAISE_RATE * 100,
            'next_raise_at' => $employment->nextRaiseAt(),
        ];
    }
}
