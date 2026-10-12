<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

class TrustConfiguredProxies extends TrustProxies
{
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO;

    protected function setTrustedProxyIpAddresses(Request $request): void
    {
        // Config is loaded at this stage and survives production config caching.
        // Never infer proxy trust from the caller's Host header.
        $proxies = array_values(array_filter(array_map('trim', explode(',', (string) config('app.trusted_proxies', '')))));
        foreach ($proxies as $proxy) {
            $parts = explode('/', $proxy, 2);
            $ip = $parts[0];
            $maxPrefix = str_contains($ip, ':') ? 128 : 32;
            if (!filter_var($ip, FILTER_VALIDATE_IP) || (isset($parts[1])
                && (!ctype_digit($parts[1]) || (int) $parts[1] < 1 || (int) $parts[1] > $maxPrefix))) {
                throw new \InvalidArgumentException('TRUSTED_PROXIES must contain explicit IP addresses or CIDR networks.');
            }
        }
        // Render routes all public requests through its gateway. Its address is
        // dynamic, so trust that immediate hop only in the explicit Render mode.
        if ($proxies === [] && config('app.render')) {
            $gateway = $request->server->get('REMOTE_ADDR');
            if (filter_var($gateway, FILTER_VALIDATE_IP)) {
                $proxies = [$gateway];
            }
        }
        $request::setTrustedProxies($proxies, $this->headers);
    }
}
