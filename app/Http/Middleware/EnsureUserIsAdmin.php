<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            Auth::logout();

            return redirect()
                ->route('filament.admin.auth.login') // pastikan sesuai route login
                ->with('error', 'Akses ditolak. Hanya admin yang diperbolehkan.');
        }

        return $next($request);
    }
}
