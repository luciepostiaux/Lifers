<?php

namespace App\Http\Middleware;

use App\Services\AccountBanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RejectBannedAccess
{
    public function __construct(private readonly AccountBanService $bans) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $this->bans->isEmailBanned($request->user()->email)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->setUserResolver(fn () => null);
            Auth::forgetGuards();
            Auth::shouldUse('web');

            abort(Response::HTTP_FORBIDDEN, 'Ce compte a été banni de Lifers.');
        }

        if ($request->routeIs('login.store', 'register.store', 'password.email')) {
            if (
                $this->bans->isEmailBanned($request->input('email'))
                || $this->bans->isIpAddressBanned($request->ip())
            ) {
                throw ValidationException::withMessages([
                    'email' => 'Cette inscription ou connexion n’est pas autorisée.',
                ]);
            }
        }

        return $next($request);
    }
}
