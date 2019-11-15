<?php

namespace Loglia\LaravelClient\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Loglia\LaravelClient\Sticky\StickyContext;
use Symfony\Component\HttpFoundation\Response;

class LogHttp
{
    /**
     * Holds the start time of the request. Used for calculating how long it took to return a response.
     *
     * @var int
     */
    private $start;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return Response
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        StickyContext::add('__loglia', [
            'request' => [
                'uuid' => Uuid::uuid4()->toString()
            ]
        ]);

        $this->start = microtime(true);

        return $next($request);
    }

    /**
     * Sends the HTTP log to Loglia once the HTTP response is sent.
     *
     * @param $request
     * @param $response
     * @return array
     * @throws \Exception
     */
    public function terminate($request, $response)
    {
        $payload = [
            '__loglia' => [
                'type' => 'http',
                'request' => $this->requestProperties($request),
                'response' => $this->responseProperties($response)
            ]
        ];

        try {
            Log::info('Handled HTTP request', $payload);
        } catch (\Exception $e) {
            Log::error('Exception thrown while logging HTTP request', [
                'exception' => $e
            ]);
        }

        return $payload;
    }

    /**
     * Returns the request properties to log.
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function requestProperties(Request $request)
    {
        return [
            'url' => $request->getPathInfo(),
            'route' => $request->route()->uri,
            'method' => $request->method(),
            'client-ip' => $request->getClientIp(),
            'headers' => $this->normalizeHeaders($request->headers->all())
        ];
    }

    /**
     * Returns the response properties to log.
     *
     * @param Response $response
     * @return array
     */
    private function responseProperties(Response $response)
    {
        return [
            'status' => $response->getStatusCode(),
            'took' => (int) ceil((microtime(true) - $this->start) * 1000),
            'size' => $this->responseSize($response),
            'headers' => $this->normalizeHeaders($response->headers->all())
        ];
    }

    /**
     * Returns the response size.
     *
     * @param Response $response
     * @return int
     */
    private function responseSize(Response $response)
    {
        if ($size = $response->headers->get('content-length')) {
            return $size;
        }

        return strlen($response->getContent());
    }

    /**
     * Normalizes request or response headers into a sane format, and applies the header blacklist.
     *
     * @param array $headers
     * @return array
     */
    private function normalizeHeaders(array $headers)
    {
        $headerBlacklist = config('loglia.http.header_blacklist', []);

        return collect($headers)
            ->map(function ($value, $header) use ($headerBlacklist) {
                if (in_array($header, $headerBlacklist)) {
                    return '[redacted]';
                }

                return $value[0];
            })
            ->filter(function ($value) {
                return ! empty($value);
            })
            ->toArray();
    }
}
