<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($user->hasRole('agency_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('super_admin')) {
            return redirect()->route('superadmin.dashboard');
        }

        return $next($request);
    }
}
