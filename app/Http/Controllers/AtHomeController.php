<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AtHomeController extends Controller
{
    public function index()
    {
        return Inertia::render('AtHome/Index');
    }
}
