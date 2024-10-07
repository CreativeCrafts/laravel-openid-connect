<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AccessTokenData;

covers(AccessTokenData::class);

it('constructs AccessTokenData with valid parameters', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code'
    ];
    $authorizationCode = 'authCode123';
    $issuerUrl = 'https://example.com';

    $accessTokenData = new AccessTokenData($config, $authorizationCode, $issuerUrl);

    expect($accessTokenData)->toBeInstanceOf(AccessTokenData::class);
});

it('returns correct array representation', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code'
    ];
    $authorizationCode = 'authCode123';
    $issuerUrl = 'https://example.com';

    $accessTokenData = new AccessTokenData($config, $authorizationCode, $issuerUrl);

    $expectedArray = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code',
        'code' => $authorizationCode,
    ];

    expect($accessTokenData->toArray())->toBe($expectedArray);
});

it('returns correct access token URL', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code'
    ];
    $authorizationCode = 'authCode123';
    $issuerUrl = 'https://example.com';

    $accessTokenData = new AccessTokenData($config, $authorizationCode, $issuerUrl);

    $expectedUrl = $issuerUrl . '/token';
    expect($accessTokenData->url())->toBe($expectedUrl);
});

it('handles special characters in authorization code', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code'
    ];
    $authorizationCode = 'authCode&123!';
    $issuerUrl = 'https://example.com';

    $accessTokenData = new AccessTokenData($config, $authorizationCode, $issuerUrl);

    $expectedArray = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code',
        'code' => $authorizationCode,
    ];

    expect($accessTokenData->toArray())->toBe($expectedArray);
});

it('constructs access token URL with different issuer URL', function () {
    $config = [
        'client_id' => 'my-client-id',
        'client_secret' => 'my-client-secret',
        'redirect_uri' => 'https://example.com/callback',
        'grant_type' => 'authorization_code'
    ];
    $authorizationCode = 'authCode123';
    $issuerUrl = 'https://another-provider.com';

    $accessTokenData = new AccessTokenData($config, $authorizationCode, $issuerUrl);

    $expectedUrl = $issuerUrl . '/token';
    expect($accessTokenData->url())->toBe($expectedUrl);
});
