<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SocialController extends Controller
{
    public function index()
    {
        return Inertia::render('Social/Index');
    }
}
