<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\TrustConfiguredProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

class ResponseSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_restrict_script_execution_framing_and_shared_caching(): void
    {
        $response = $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self';", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_only_configured_proxy_addresses_can_supply_forwarded_headers(): void
    {
        config(['app.trusted_proxies' => '127.0.0.1']);
        foreach (['127.0.0.1' => true, '192.0.2.10' => false] as $address => $trusted) {
            $request = Request::create('http://games.example.test/', 'GET', [], [], [], [
                'REMOTE_ADDR' => $address, 'HTTP_X_FORWARDED_PROTO' => 'https',
                'HTTP_X_FORWARDED_FOR' => '198.51.100.20',
            ]);
            (new TrustConfiguredProxies)->handle($request, function (Request $request) use ($trusted, $address) {
                $this->assertSame($trusted, $request->isSecure());
                $this->assertSame($trusted ? '198.51.100.20' : $address, $request->ip());
                return response('ok');
            });
        }
        Request::setTrustedProxies([], 0);
    }

    public function test_dynamic_gateway_trust_requires_render_mode(): void
    {
        config(['app.trusted_proxies' => '']);
        foreach ([false, true] as $render) {
            config(['app.render' => $render]);
            $request = Request::create('http://lioloto.onrender.com/', 'GET', [], [], [], [
                'REMOTE_ADDR' => '10.0.0.5', 'HTTP_X_FORWARDED_PROTO' => 'https',
                'HTTP_X_FORWARDED_FOR' => '198.51.100.20',
            ]);
            (new TrustConfiguredProxies)->handle($request, function (Request $request) use ($render) {
                $this->assertSame($render, $request->isSecure());
                $this->assertSame($render ? '198.51.100.20' : '10.0.0.5', $request->ip());
                return response('ok');
            });
        }
        Request::setTrustedProxies([], 0);
    }
}
