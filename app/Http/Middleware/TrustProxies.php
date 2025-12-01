<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust ALL proxies (Traefik in Coolify).
     */
    protected $proxies = '*';

    /**
     * Use AWS_ELB headers (Laravel recommends this for reverse proxies).
     */
    protected $headers = Request::HEADER_X_FORWARDED_AWS_ELB;
}
