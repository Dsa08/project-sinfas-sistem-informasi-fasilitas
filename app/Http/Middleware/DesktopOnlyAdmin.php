<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DesktopOnlyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $userAgent = strtolower($request->userAgent() ?? '');
        $user = $request->user();
        $mobileClientHint = $request->header('Sec-CH-UA-Mobile') === '?1';
        $mobileUserAgent = preg_match(
            '/android|iphone|ipad|ipod|mobile|iemobile|opera mini|blackberry|webos|silk|kindle/',
            $userAgent
        ) === 1;

        if ($user && in_array($user->role, ['admin_sarana', 'admin_sistem'], true) && ($mobileClientHint || $mobileUserAgent)) {
            return response()->view('errors.admin-desktop-only', status: 403);
        }

        return $next($request);
    }
}
