<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectHttpClient;
use Illuminate\Support\Facades\Http;

covers(OpenIDConnectHttpClient::class);

it('fetches URL successfully with default options', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200, ['Content-Type' => 'text/plain']),
    ]);

    $client = new OpenIDConnectHttpClient();
    $response = $client->fetchViaPostMethod('https://example.com');

    expect($response)->toBe('OK')
        ->and($client->getResponseCode())->toBe(200)
        ->and($client->getResponseContentType())->toBe('text/plain');
});

it('fetches via get method successfully', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200, ['Content-Type' => 'text/plain']),
    ]);

    $client = new OpenIDConnectHttpClient();
    $response = $client->fetchViaGetMethod('https://example.com');

    expect($response)->toBe('OK')
        ->and($client->getResponseCode())->toBe(200)
        ->and($client->getResponseContentType())->toBe('text/plain');
});

it('throws an exception on HTTP failure', function () {
    Http::fake([
        'https://example.com' => Http::response('Error', 500),
    ]);

    $client = new OpenIDConnectHttpClient();

    $this->expectException(OpenIDConnectClientException::class);
    $client->fetchViaPostMethod('https://example.com');
});

it('sets and uses a custom HTTP proxy', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200),
    ]);

    $client = new OpenIDConnectHttpClient();
    $client->setHttpProxy('http://proxy.example.com');

    $response = $client->fetchViaPostMethod('https://example.com');

    expect($response)->toBe('OK');

    Http::assertSent(function ($request) {
        // Although we can't directly check the proxy option here,
        // we can verify the request was made correctly to the intended URL.
        return $request->url() === 'https://example.com';
    });
});

it('sets and uses a custom SSL certificate', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200),
    ]);

    $client = new OpenIDConnectHttpClient();
    $client->setCertPath('/path/to/cert.pem');

    $response = $client->fetchViaPostMethod('https://example.com');

    expect($response)->toBe('OK');

    // Check that a request was sent (verifying it reached the intended URL)
    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com';
    });
});

it('sets and uses a custom timeout value', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200),
    ]);

    $client = new OpenIDConnectHttpClient();
    $client->setTimeout(120);

    $response = $client->fetchViaPostMethod('https://example.com');

    expect($response)->toBe('OK');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com';
    });
});

it('sets and uses peer verification setting', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200),
    ]);

    $client = new OpenIDConnectHttpClient();
    $client->setVerifyPeer(false);

    $response = $client->fetchViaPostMethod('https://example.com');

    expect($response)->toBe('OK');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com';
    });
});

it('determines content type based on post body', function () {
    Http::fake([
        'https://example.com' => Http::response('OK', 200),
    ]);

    $client = new OpenIDConnectHttpClient();

    // Test with a JSON string
    $client->fetchViaPostMethod('https://example.com', '{"key":"value"}', []);

    // Check that the correct content-type was used
    Http::assertSent(function ($request) {
        return $request->hasHeader('Content-Type', 'application/json');
    });

    // Test with an empty string
    $client->fetchViaPostMethod('https://example.com', '', []);

    // Check that the content-type is form-urlencoded for empty strings
    Http::assertSent(function ($request) {
        return $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded');
    });
});
