<?php



namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login'); // Redirect to login if not authenticated
        }

        $user = Auth::user();

        if ($user->hasRole($role)) {
            return $next($request);  // Allow access if role matches
        }

        return redirect('/');  // Deny access otherwise
    }
}
