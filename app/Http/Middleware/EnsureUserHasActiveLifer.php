<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveLifer
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->activeLifer()->exists()) {
            return redirect()->route('character.create');
        }

        return $next($request);
    }
}
