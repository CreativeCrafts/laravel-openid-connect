<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\DataTransferObjects\AuthorizationData;

it('throws InvalidArgumentException if client secret is missing in accessTokenData', function () {
    expect(static fn () => (new AuthorizationData(
        'test_client_id',
        null,
        'https://provider.com',
        'http://localhost/callback',
        'authorization_code'
    )
    )
        ->accessTokenData())
        ->toThrow(InvalidArgumentException::class, 'Client secret is required for refresh token grant.');
});

it('throws InvalidArgumentException if redirect URI is missing in accessTokenData', function () {
    expect(
        static fn () => (new AuthorizationData(
            'test_client_id',
            'test_client_secret',
            'https://provider.com',
            null,
            'authorization_code'
        ))
            ->accessTokenData()
    )
            ->toThrow(InvalidArgumentException::class, 'Redirect URI is required for authorization code grant.');
});

it('throws InvalidArgumentException if authorization code is missing in accessTokenData', function () {
    expect(
        static fn () => (new AuthorizationData(
            'test_client_id',
            'test_client_secret',
            'https://provider.com',
            'http://localhost/callback',
            null
        ))
            ->accessTokenData()
    )
        ->toThrow(InvalidArgumentException::class, 'Authorization code is required for authorization code grant.');
});

it('returns access token data with valid inputs', function () {
    $authorizationData = new AuthorizationData(
        clientId: 'test_client_id',
        clientSecret: 'test_client_secret',
        url: 'https://provider.com',
        redirectUri: 'http://localhost/callback',
        code: 'authorization_code'
    );

    $data = $authorizationData->accessTokenData();

    expect($data)->toEqual([
        'grant_type' => 'authorization_code',
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'redirect_uri' => 'http://localhost/callback',
        'code' => 'authorization_code',
    ]);
});

it('throws InvalidArgumentException if issuer URL is missing', function () {
    expect(static fn () => (new AuthorizationData(clientId: 'test_client_id'))->issuerUrl())
        ->toThrow(InvalidArgumentException::class, 'Issuer URL is required.');
});

it('returns the issuer URL with valid input', function () {
    $authorizationData = new AuthorizationData(
        clientId: 'test_client_id',
        url: 'https://provider.com'
    );

    expect($authorizationData->issuerUrl())->toEqual('https://provider.com');
});

it('throws InvalidArgumentException if client secret is missing in refreshTokenData', function () {
    expect(
        static fn () => (new AuthorizationData(
            'test_client_id',
            null,
            'https://provider.com',
            null,
            null,
            'refresh_token'
        ))
            ->refreshTokenData()
    )
            ->toThrow(InvalidArgumentException::class, 'Client secret is required for refresh token grant.');
});

it('returns refresh token data with valid inputs', function () {
    $authorizationData = new AuthorizationData(
        clientId: 'test_client_id',
        clientSecret: 'test_client_secret',
        refreshToken: 'refresh_token'
    );

    $data = $authorizationData->refreshTokenData();

    expect($data)->toEqual([
        'grant_type' => 'refresh_token',
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'refresh_token' => 'refresh_token',
    ]);
});

it('throws InvalidArgumentException if redirect URI is missing in queryParameters', function () {
    expect(
        static fn () => (new AuthorizationData(
            clientId: 'test_client_id',
            url: 'https://provider.com',
            responseType: 'code',
            scope: 'openid',
            state: 'xyz'
        ))
            ->queryParameters()
    )
        ->toThrow(InvalidArgumentException::class, 'Redirect URI is required for authorization code grant.');
});

it('throws InvalidArgumentException if response type is missing in queryParameters', function () {
    expect(
        static fn () => (new AuthorizationData(
            clientId: 'test_client_id',
            url: 'https://provider.com',
            redirectUri: 'http://localhost/callback',
            scope: 'openid',
            state: 'xyz'
        ))
            ->queryParameters()
    )
        ->toThrow(InvalidArgumentException::class, 'Response type is required for authorization code grant.');
});

it('throws InvalidArgumentException if scope is missing in queryParameters', function () {
    expect(
        static fn () => (new AuthorizationData(
            clientId: 'test_client_id',
            url: 'https://provider.com',
            redirectUri: 'http://localhost/callback',
            responseType: 'code',
            state: 'xyz'
        ))
            ->queryParameters()
    )
        ->toThrow(InvalidArgumentException::class, 'Scope is required for authorization code grant.');
});

it('throws InvalidArgumentException if state is missing in queryParameters', function () {
    expect(
        static fn () => (new AuthorizationData(
            clientId: 'test_client_id',
            url: 'https://provider.com',
            redirectUri: 'http://localhost/callback',
            responseType: 'code',
            scope: 'openid'
        ))
            ->queryParameters()
    )
        ->toThrow(InvalidArgumentException::class, 'State is required for authorization code grant.');
});

it('returns query parameters with valid inputs', function () {
    $authorizationData = new AuthorizationData(
        clientId: 'test_client_id',
        url: 'https://provider.com',
        redirectUri: 'http://localhost/callback',
        responseType: 'code',
        scope: 'openid',
        state: 'xyz'
    );

    $queryParams = $authorizationData->queryParameters();

    expect($queryParams)->toEqual(http_build_query([
        'client_id' => 'test_client_id',
        'redirect_uri' => 'http://localhost/callback',
        'response_type' => 'code',
        'scope' => 'openid',
        'state' => 'xyz',
    ]));
});
