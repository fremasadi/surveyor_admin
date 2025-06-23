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

        // Jika user tidak ada atau bukan admin
        if (!$user || $user->role !== 'admin') {
            Auth::logout();

            // redirect langsung ke URL filament admin login (bukan pakai route())
            return redirect('/admin/login')
                ->with('error', 'Akses ditolak. Hanya admin yang diperbolehkan.');
        }

        return $next($request);
    }
}
