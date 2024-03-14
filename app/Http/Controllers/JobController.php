<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::with('diploma')->get();
        $user = Auth::user();
        $perso = $user->perso()->with(['job.diploma'])->first();

        return Inertia::render('Job/Index', [
            'jobs' => $jobs,
            'userDiplomas' => $perso ? $perso->diplomas : collect(),
            'currentJob' => $perso ? $perso->job : null,
        ]);
    }


    public function apply(ApplyJobRequest $request, $jobId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {

            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        $job = Job::findOrFail($jobId);

        if (!$personnage->diplomas->contains('id', $job->diplomas_id)) {
            return redirect()->back()->withErrors(['msg' => 'Tu dois avant tout réaliser les études liées.']);
        }

        $personnage->jobs_id = $jobId;
        $personnage->save();
        return redirect()->back()->with('message', 'Vous avez postulé avec succès pour ce métier.');
    }

    public function showCurrentJob($jobId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        $job = Job::findOrFail($jobId);
        if (!$job) {
            return redirect()->route('job');
        }

        return Inertia::render('Job/Current', [
            'jobDetails' => $job,
        ]);
    }


    public function resign()
    {
        $user = Auth::user();
        $personnage = $user->perso;


        if ($personnage && $personnage->job) {
            $personnage->jobs_id = null;
            $personnage->save();

            return redirect()->route('job')->with('message', 'Vous avez démissionné avec succès de votre poste.');
        } else {
            return redirect()->route('job')->withErrors(['msg' => 'Vous n\'avez pas de poste actuel duquel démissionner.']);
        }
    }
    
    public function changeJob(Request $request, $newJobId)
    {
        $user = Auth::user();
        $personnage = $user->perso;

        if (!$personnage) {
            return redirect()->back()->withErrors(['msg' => 'Aucun personnage associé à cet utilisateur.']);
        }

        // Trouver le nouveau job
        $newJob = Job::findOrFail($newJobId);

        // Vérifier si le personnage possède le diplôme requis pour le nouveau travail
        if (!$personnage->diplomas->contains('id', $newJob->diplomas_id)) {
            return redirect()->back()->withErrors(['msg' => 'Tu dois avant tout réaliser les études liées.']);
        }

        // Commencer la transaction
        DB::beginTransaction();
        try {
            // Si le personnage a un job, le résigner
            if ($personnage->job) {
                $personnage->jobs_id = null;
                $personnage->save();
            }

            // Postuler pour le nouveau job
            $personnage->jobs_id = $newJobId;
            $personnage->save();

            // Valider la transaction
            DB::commit();
            return redirect()->route('job.index')->with('message', 'Changement de travail effectué avec succès.');
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => 'Une erreur est survenue lors du changement de travail.']);
        }
    }
}
