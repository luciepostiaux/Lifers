<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Inertia::share([
            'perso' => function () {
                $user = Auth::user();
                if ($user) {
                    $perso = $user->perso()->first();
                    return $perso ? $perso->toArray() : null;
                }
                return null;
            },
        ]);
    }
}
