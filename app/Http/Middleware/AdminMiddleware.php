<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Belum login
        if (!$user) {
            return redirect()->route('login')->withErrors('Silakan login terlebih dahulu.');
        }

        // Pastikan role admin
        if ((int) $user->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
