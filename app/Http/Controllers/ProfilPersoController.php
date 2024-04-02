<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfilPersoController extends Controller
{
    public function index()
    {
        return Inertia::render('ProfilPerso/Index');
    }
}
