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
            'lifer' => function () {
                $user = Auth::user();
                if ($user) {
                    return $user->activeLifer()
                        ->first(['lifers.id', 'lifers.first_name', 'lifers.last_name'])
                        ?->only(['id', 'first_name', 'last_name']);
                }
                return null;
            },
            'unreadPrivateMessagesCount' => function () {
                $lifer = Auth::user()?->activeLifer()->first();

                return $lifer?->unreadPrivateMessagesCount() ?? 0;
            },
        ]);
    }
}
