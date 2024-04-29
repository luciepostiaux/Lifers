<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BugIdeaController extends Controller
{
    public function index()
    {
        $suggestions = Suggestion::all();
        $bugs = Bug::all();
        return Inertia::render('BugIdea/Index', [
            'suggestions' => $suggestions,
            'bugs' => $bugs,

        ]);
    }
}
