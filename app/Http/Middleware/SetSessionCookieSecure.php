<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetSessionCookieSecure
{
    /**
     * Handle the given request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // When SESSION_SECURE_COOKIE is not defined the cookie has to match the
        // scheme the site is actually served with. Behind the Coolify proxy
        // this is the X-Forwarded-Proto header, which TrustProxies already
        // resolved at this point. If the flag was forced with "true" while the
        // site runs over plain HTTP, the browser discards the cookie and the
        // session is lost on every request.
        if (config('session.secure') === null) {
            config(['session.secure' => $request->isSecure()]);
        }

        return $next($request);
    }
}
