<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CharacterController extends Controller
{
    public function create()
    {
        return Inertia::render('Character/Create');
    }
}
