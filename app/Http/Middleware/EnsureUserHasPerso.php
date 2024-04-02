<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPerso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->perso === null && !$request->is('character/create')) {
            return redirect()->route('character.create');
        }

        return $next($request);
    }
}
