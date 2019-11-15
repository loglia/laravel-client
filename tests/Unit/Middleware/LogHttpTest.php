<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase;
use Loglia\LaravelClient\Middleware\LogHttp;
use Loglia\LaravelClient\Sticky\StickyContext;

class LogHttpTest extends TestCase
{
    /**
     * @var LogHttp
     */
    private $middleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->middleware = new LogHttp;

        // Clear the sticky context before each test run.
        StickyContext::clear();
    }

    /** @test */
    public function it_logs_request_uuid_using_sticky_context()
    {
        $request = new Request;
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});

        $this->assertTrue(Uuid::isValid(StickyContext::all()['--loglia']['request']['uuid']), 'Request UUID must be a valid UUID.');
    }

    /** @test */
    public function it_logs_request_url()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/users/200a1d46-ac85-4619-9eca-1ae59c6bc366']);
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('/users/200a1d46-ac85-4619-9eca-1ae59c6bc366', $log['--loglia']['request']['url'], 'Request URL must match the current URL.');
    }

    /** @test */
    public function it_logs_request_route()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('/users/{uuid}', $log['--loglia']['request']['route'], 'Request route must match the current route.');
    }

    /** @test */
    public function it_logs_request_method()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('GET', $log['--loglia']['request']['method'], 'Request method must match the HTTP method.');
    }

    /** @test */
    public function it_logs_request_client_ip()
    {
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '210.34.170.149']);
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertSame('210.34.170.149', $log['--loglia']['request']['client-ip'], 'Client IP must match the IP address of the client.');
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

        $this->assertSame($expectedHeaders, $log['--loglia']['request']['headers'], 'HTTP headers must match the request headers.');
    }

    /** @test */
    public function it_logs_response_statuses()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response('', 404));

        $this->assertSame(404, $log['--loglia']['response']['status'], 'HTTP response status must match response status.');
    }

    /** @test */
    public function it_logs_response_time_taken()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response);

        $this->assertNotNull($log['--loglia']['response']['took'], 'HTTP response time taken must be present.');
    }

    /** @test */
    public function it_logs_response_size()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response('hello world'));

        $this->assertSame(11, $log['--loglia']['response']['size'], 'HTTP response size must be accurate.');
    }

    /** @test */
    public function it_logs_response_size_with_multibyte_characters()
    {
        $request = new Request();
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response('hello world 👋'));

        $this->assertSame(16, $log['--loglia']['response']['size'], 'Multibyte characters should be supported in response size calculation.');
    }

    /** @test */
    public function it_logs_response_headers()
    {
        $request = new Request;
        $request->setRouteResolver($this->routeResolver());

        $this->middleware->handle($request, function () {});
        $log = $this->middleware->terminate($request, new Response('', 200, ['x-testing' => 'foo']));

        $this->assertSame('foo', $log['--loglia']['response']['headers']['x-testing'], 'HTTP headers must match the response headers.');
    }

    private function routeResolver()
    {
        return function () {
            return new Route('get', '/users/{uuid}', function () {});
        };
    }
}
