<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder al recurso.');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Su cuenta se encuentra inactiva. Contacte al administrador.'], 403);
            }
            return redirect()->route('login')->with('error', 'Su cuenta se encuentra inactiva. Contacte al administrador.');
        }

        $userRoleSlug = $user->role ? $user->role->slug : null;

        if (!$userRoleSlug || !in_array($userRoleSlug, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acceso no autorizado. Nivel de permisos insuficiente.'], 403);
            }
            abort(403, 'Acceso denegado: No posee los permisos requeridos.');
        }

        return $next($request);
    }
}