<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\RefreshTokenData;

it('constructs RefreshTokenData with valid parameters', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token'
    ];
    $refreshToken = 'refreshToken123';
    $issuerUrl = 'https://example.com';

    $refreshTokenData = new RefreshTokenData($config, $refreshToken, $issuerUrl);

    expect($refreshTokenData)->toBeInstanceOf(RefreshTokenData::class);
});

it('returns correct array representation', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token'
    ];
    $refreshToken = 'refreshToken123';
    $issuerUrl = 'https://example.com';

    $refreshTokenData = new RefreshTokenData($config, $refreshToken, $issuerUrl);

    $expectedArray = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
    ];

    expect($refreshTokenData->toArray())->toBe($expectedArray);
});

it('returns correct refresh token URL', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token'
    ];
    $refreshToken = 'refreshToken123';
    $issuerUrl = 'https://example.com';

    $refreshTokenData = new RefreshTokenData($config, $refreshToken, $issuerUrl);

    $expectedUrl = $issuerUrl . '/token';
    expect($refreshTokenData->url())->toBe($expectedUrl);
});

it('handles special characters in refresh token', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token'
    ];
    $refreshToken = 'refresh&Token!';
    $issuerUrl = 'https://example.com';

    $refreshTokenData = new RefreshTokenData($config, $refreshToken, $issuerUrl);

    $expectedArray = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
    ];

    expect($refreshTokenData->toArray())->toBe($expectedArray);
});

it('constructs refresh token URL with different issuer URL', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'grant_type' => 'refresh_token'
    ];
    $refreshToken = 'refreshToken123';
    $issuerUrl = 'https://another-provider.com';

    $refreshTokenData = new RefreshTokenData($config, $refreshToken, $issuerUrl);

    $expectedUrl = $issuerUrl . '/token';
    expect($refreshTokenData->url())->toBe($expectedUrl);
});
