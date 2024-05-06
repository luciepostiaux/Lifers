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
    public function showSuggestion($id)
    {
        $suggestion = Suggestion::with('comments')->findOrFail($id);
        return Inertia::render('BugIdea/ShowSuggestion', [
            'suggestion' => $suggestion,
            'comments' => $suggestion->comments,
        ]);
    }

    public function showBug($id)
    {
        $bug = Bug::with('comments')->findOrFail($id);
        return Inertia::render('BugIdea/ShowBug', [
            'bug' => $bug,
            'comments' => $bug->comments,
        ]);
    }
}
