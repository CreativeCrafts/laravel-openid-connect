<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AuthorizationData;

covers(AuthorizationData::class);

it('constructs AuthorizationData with valid parameters', function () {
    $config = [
        'redirect_uri' => 'https://example.com/callback',
        'response_type' => 'code',
        'scope' => 'openid profile',
        'state' => 'randomState123'
    ];
    $issuerUrl = 'https://example.com';

    $authorizationData = new AuthorizationData($config, $issuerUrl);

    expect($authorizationData)->toBeInstanceOf(AuthorizationData::class);
});

it('returns correct query parameters', function () {
    $config = [
        'redirect_uri' => 'https://example.com/callback',
        'response_type' => 'code',
        'scope' => 'openid profile',
        'state' => 'randomState123'
    ];
    $issuerUrl = 'https://example.com';

    $authorizationData = new AuthorizationData($config, $issuerUrl);
    expect($authorizationData->queryParameters())->toBe(http_build_query($config));
});

it('returns correct authorization URL', function () {
    $config = [
        'redirect_uri' => 'https://example.com/callback',
        'response_type' => 'code',
        'scope' => 'openid profile',
        'state' => 'randomState123'
    ];
    $issuerUrl = 'https://example.com';

    $authorizationData = new AuthorizationData($config, $issuerUrl);

    $expectedUrl = $issuerUrl . '/authorize?' . http_build_query($config);
    expect($authorizationData->authorizationUrl())->toBe($expectedUrl);
});

it('handles special characters in query parameters', function () {
    $config = [
        'redirect_uri' => 'https://example.com/callback',
        'response_type' => 'code',
        'scope' => 'openid profile',
        'state' => 'randomState123',
        'custom_param' => 'value with spaces & symbols!'
    ];
    $issuerUrl = 'https://example.com';

    $authorizationData = new AuthorizationData($config, $issuerUrl);

    $expectedQuery = http_build_query($config);
    expect($authorizationData->queryParameters())->toBe($expectedQuery);
});
