<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyStudyRequest;
use App\Models\LiferGameState;
use App\Models\LiferStudyEnrollment;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StudyController extends Controller
{
    public function index()
    {
        $lifer = $this->activeLifer([
            'diplomas',
            'gameState',
            'activeStudyEnrollment.study.awardedDiploma',
            'activeStudyEnrollment.study.requiredDiploma',
            'activeStudyEnrollment.study.place',
        ]);
        $enrollment = $lifer->activeStudyEnrollment;

        return Inertia::render('Study/Index', [
            'studies' => Study::with(['awardedDiploma', 'requiredDiploma', 'place'])->get(),
            'currentStudy' => $enrollment?->study ? [
                'id' => $enrollment->study->id,
                'name' => $enrollment->study->name,
                'description' => $enrollment->study->short_description,
                'start_date' => $enrollment->started_at,
                'end_date' => $enrollment->ends_at,
                'image_path' => $enrollment->study->image_path,
                'awarded_diploma' => $enrollment->study->awardedDiploma,
                'required_diploma' => $enrollment->study->requiredDiploma,
                'place' => $enrollment->study->place,
            ] : null,
            'persoDiplomas' => $lifer->diplomas,
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function showCurrentStudy(int $id)
    {
        $lifer = $this->activeLifer(['gameState']);
        $enrollment = $lifer
            ->activeStudyEnrollment()
            ->with(['study.awardedDiploma', 'study.requiredDiploma', 'study.place'])
            ->where('study_id', $id)
            ->firstOrFail();

        return Inertia::render('Study/Current', [
            'studyDetails' => $enrollment->study,
            'enrollmentDetails' => $enrollment,
            'canClaimDiploma' => $enrollment->ends_at->isPast(),
            'money' => $lifer->gameState?->money,
        ]);
    }

    public function enroll(ApplyStudyRequest $request, int $studyId)
    {
        $lifer = $this->activeLifer(['diplomas']);
        $study = Study::findOrFail($studyId);

        if ($study->required_diploma_id && ! $lifer->diplomas->contains('id', $study->required_diploma_id)) {
            throw ValidationException::withMessages([
                'study' => 'Vous devez posséder le diplôme requis pour suivre cette étude.',
            ]);
        }

        DB::transaction(function () use ($lifer, $study) {
            $state = LiferGameState::query()->lockForUpdate()->findOrFail($lifer->id);

            if ($state->money < $study->price) {
                throw ValidationException::withMessages([
                    'study' => 'Vous n’avez pas suffisamment d’argent pour cette étude.',
                ]);
            }

            LiferStudyEnrollment::query()
                ->where('lifer_id', $lifer->id)
                ->where('status', LiferStudyEnrollment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->update([
                    'status' => LiferStudyEnrollment::STATUS_LEFT,
                    'ended_at' => now(),
                ]);

            $state->decrement('money', $study->price);

            LiferStudyEnrollment::create([
                'lifer_id' => $lifer->id,
                'study_id' => $study->id,
                'started_at' => now(),
                'ends_at' => now()->addDays($study->duration_days),
                'status' => LiferStudyEnrollment::STATUS_ACTIVE,
            ]);
        });

        return redirect()->route('study.index')->with('message', 'Inscription à l’étude réussie.');
    }

    public function resign()
    {
        $lifer = $this->activeLifer();
        $updated = $lifer->activeStudyEnrollment()->update([
            'status' => LiferStudyEnrollment::STATUS_LEFT,
            'ended_at' => now(),
        ]);

        if (! $updated) {
            return back()->withErrors(['msg' => 'Vous ne suivez actuellement aucune étude.']);
        }

        return redirect()->route('study.index')->with('message', 'Vous avez quitté l’étude.');
    }

    public function claimDiploma(Request $request, int $studyId)
    {
        $lifer = $this->activeLifer();

        DB::transaction(function () use ($lifer, $studyId) {
            $enrollment = LiferStudyEnrollment::query()
                ->where('lifer_id', $lifer->id)
                ->where('study_id', $studyId)
                ->where('status', LiferStudyEnrollment::STATUS_ACTIVE)
                ->with('study.awardedDiploma')
                ->lockForUpdate()
                ->first();

            if (! $enrollment) {
                throw ValidationException::withMessages([
                    'study' => 'Aucune inscription active ne correspond à cette étude.',
                ]);
            }

            if ($enrollment->ends_at->isFuture()) {
                throw ValidationException::withMessages([
                    'study' => 'La date de fin de cette étude n’est pas encore atteinte.',
                ]);
            }

            $lifer->diplomas()->syncWithoutDetaching([
                $enrollment->study->awarded_diploma_id => ['earned_at' => now()],
            ]);

            $enrollment->update([
                'status' => LiferStudyEnrollment::STATUS_COMPLETED,
                'ended_at' => now(),
            ]);
        });

        return redirect()->route('study.index')->with('message', 'Diplôme récupéré avec succès.');
    }
}
