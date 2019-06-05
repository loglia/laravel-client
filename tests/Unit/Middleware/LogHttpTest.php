<?php

namespace Retrospekt\LaravelClient\Tests\Unit\Monolog;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase;
use Retrospekt\LaravelClient\Middleware\LogHttp;

class LogHttpTest extends TestCase
{
    /**
     * @var LogHttp
     */
    private $middleware;

    public function setUp()
    {
        parent::setUp();

        $this->middleware = new LogHttp;
    }

    /** @test */
    public function it_logs_request_uuid()
    {
        $request = new Request;
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertTrue(Uuid::isValid($log['--retrospekt']['request']['uuid']), 'Request UUID must be a valid UUID.');
    }

    /** @test */
    public function it_logs_request_url()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/users/200a1d46-ac85-4619-9eca-1ae59c6bc366']);
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('/users/200a1d46-ac85-4619-9eca-1ae59c6bc366', $log['--retrospekt']['request']['url'], 'Request URL must match the current URL.');
    }

    /** @test */
    public function it_logs_request_route()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('/users/{uuid}', $log['--retrospekt']['request']['route'], 'Request route must match the current route.');
    }

    /** @test */
    public function it_logs_request_method()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('GET', $log['--retrospekt']['request']['method'], 'Request method must match the HTTP method.');
    }

    /** @test */
    public function it_logs_request_client_ip()
    {
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '210.34.170.149']);
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('210.34.170.149', $log['--retrospekt']['request']['client-ip'], 'Client IP must match the IP address of the client.');
    }

    /** @test */
    public function it_logs_request_headers()
    {
        $request = new Request([], [], [], [], [], ['HTTP_X_TESTING' => 'foo']);
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $expectedHeaders = [
            'x-testing' => 'foo'
        ];

        $this->assertSame($expectedHeaders, $log['--retrospekt']['request']['headers'], 'HTTP headers must match the request headers.');
    }

    private function routeResolver()
    {
        return function () {
            return new Route('get', '/users/{uuid}', function () {});
        };
    }
}
