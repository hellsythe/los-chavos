<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfigureSessionCookie
{
    /**
     * Handle the given request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $this->configureSecureFlag($request);
        $this->configureDomain($request);

        return $next($request);
    }

    /**
     * Marca la cookie como segura solo si la peticion llega por HTTPS.
     *
     * Si SESSION_SECURE_COOKIE queda en "true" mientras el sitio se sirve por
     * HTTP, el navegador descarta la cookie y la sesion se pierde en cada
     * peticion. Por eso, si no esta definido, se decide con la peticion real.
     */
    protected function configureSecureFlag(Request $request): void
    {
        if (config('session.secure') === null) {
            config(['session.secure' => $request->isSecure()]);
        }
    }

    /**
     * Descarta el dominio configurado si no corresponde al host que se esta
     * sirviendo.
     *
     * Una cookie con un Domain que no incluye al host de la peticion es
     * rechazada por el navegador, y con ella se pierde la sesion. Es tipico
     * dejar el dominio del preview anterior configurado en el servidor.
     */
    protected function configureDomain(Request $request): void
    {
        $domain = config('session.domain');

        if (! $domain) {
            return;
        }

        $domain = ltrim($domain, '.');
        $host = $request->getHost();

        if ($host !== $domain && ! Str::endsWith($host, '.'.$domain)) {
            config(['session.domain' => null]);
        }
    }
}
