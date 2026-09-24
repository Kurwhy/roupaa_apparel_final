<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json(['error' => 'Akses ditolak. Sesi berakhir atau Token tidak valid.'], 401);
            }
            return redirect('/login')->with('error', 'Silakan login untuk mengakses fitur ini.');
        }

        $userRole = $user->role;

        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['error' => 'Akses Ditolak! Anda tidak memiliki izin untuk tindakan ini.'], 403);
        }

        return redirect('/')->with('error', 'Akses Ditolak! Area ini bukan untuk peran Anda.');
    }
}