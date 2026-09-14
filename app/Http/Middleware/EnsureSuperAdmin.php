<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('super_admin')) {
            if ($user && $user->hasRole('agency_admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user && $user->hasRole('student')) {
                return redirect()->route('home');
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
