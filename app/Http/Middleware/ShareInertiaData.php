<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareInertiaData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Exemple de récupération des notifications non lues pour l'utilisateur connecté
        if (auth()->check()) {
            $unreadNotifications = auth()->user()->unreadNotifications->count();


            Inertia::share(['unreadNotifications' => function () {
                return auth()->user() ? auth()->user()->unreadNotifications->count() : 0;
            }]);
        }

        return $next($request);
    }
}
