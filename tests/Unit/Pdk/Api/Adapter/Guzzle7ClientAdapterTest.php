<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('forwards method, uri, headers and body to guzzle and returns a mapped ClientResponse', function () {
    $sent       = [];
    $mock       = new MockHandler([new Response(200, ['X-Test' => 'ok'], '{"hello":"world"}')]);
    $stack      = HandlerStack::create($mock);
    $stack->push(Middleware::history($sent));
    $client     = new Client(['handler' => $stack]);
    $adapter    = new Guzzle7ClientAdapter($client);

    $response = $adapter->doRequest('POST', 'https://api.example.test/resource', [
        'headers' => ['Authorization' => 'bearer abc'],
        'body'    => '{"foo":"bar"}',
    ]);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getBody())->toBe('{"hello":"world"}');

    expect($sent)->toHaveCount(1);
    $request = $sent[0]['request'];
    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.example.test/resource')
        ->and($request->getHeaderLine('Authorization'))->toBe('bearer abc')
        ->and((string) $request->getBody())->toBe('{"foo":"bar"}');
});

it('does not throw on non-2xx responses (http_errors disabled)', function () {
    $mock    = new MockHandler([new Response(401, [], '{"error":"unauthorized"}')]);
    $client  = new Client(['handler' => HandlerStack::create($mock)]);
    $adapter = new Guzzle7ClientAdapter($client);

    $response = $adapter->doRequest('GET', 'https://api.example.test/whoami', []);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getBody())->toBe('{"error":"unauthorized"}');
});
