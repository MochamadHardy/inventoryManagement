<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        // Super Admin memiliki hak istimewa (bypass) ke seluruh modul atau jika role user ada di daftar
        if ($user->role === 'super_admin' || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Akses ditolak (Forbidden). Role Anda ('.$user->role.') tidak memiliki izin untuk fitur ini.',
            'required_roles' => $roles,
        ], 403);
    }
}
