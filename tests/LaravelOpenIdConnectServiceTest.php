<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\LaravelOpenIdConnectService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

covers(LaravelOpenIdConnectService::class);

it('sends a simple POST request successfully', function () {
    Http::fake();
    LaravelOpenIdConnectService::post('https://example.com/token', [
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/token';
    });
});

it('sends a POST request successfully and returns the expected response', function () {
    Http::fake([
        'https://example.com/token' => Http::response(['success' => true], 200),
    ]);

    $response = LaravelOpenIdConnectService::post('https://example.com/token', [
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBe(['success' => true]);
});

it('throws ConnectionException on POST request failure', function () {
    Http::fake([
        'https://example.com/token' => Http::response(null, 500),
    ]);

    $this->expectException(ConnectionException::class);

    // Perform the POST request
    LaravelOpenIdConnectService::post('https://example.com/token', [
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
    ]);
});

it('sends a GET request successfully', function () {
    $mockResponse = Http::response([
        'user' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
    ], 200);

    Http::fake([
        'https://example.com/userinfo' => $mockResponse,
    ]);

    $response = LaravelOpenIdConnectService::get('https://example.com/userinfo', 'test_access_token');

    expect($response->status())->toBe(200)
        ->and($response->json())->toBe([
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);
});

it('throws ConnectionException on GET request failure', function () {
    Http::fake([
        'https://example.com/userinfo' => Http::response(null, 500),
    ]);

    $this->expectException(ConnectionException::class);

    LaravelOpenIdConnectService::get('https://example.com/userinfo', 'test_access_token');
});
