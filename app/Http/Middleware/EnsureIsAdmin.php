<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsAdmin
{
public function handle(Request $request, Closure $next)
{
if (!Auth::check() || !Auth::user()->hasRole('admin')) {
// Redirigez les non-administrateurs vers la page d'accueil ou une autre route appropriée
return redirect('/');
}

return $next($request);
}
}