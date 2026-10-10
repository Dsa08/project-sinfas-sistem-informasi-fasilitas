<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Batasi akun bersandi sementara agar hanya dapat mengganti sandi atau keluar. */
class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        $allowedRoutes = ['password.change.show', 'password.change.update', 'logout'];

        if ($account?->must_change_password && ! in_array($request->route()?->getName(), $allowedRoutes, true)) {
            return redirect()->route('password.change.show');
        }

        return $next($request);
    }
}
