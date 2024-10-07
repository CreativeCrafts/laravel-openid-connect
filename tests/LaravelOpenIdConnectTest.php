<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Exceptions\InvalidProviderConfigurationException;
use CreativeCrafts\LaravelOpenidConnect\LaravelOpenIdConnect;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

covers(LaravelOpenIdConnect::class);

beforeEach(function () {
    Session::start();
    $this->state = csrf_token();
    Config::set('openid-connect.providers.my_provider', [
        'authorization' => [
            'client_id' => 'test_client_id',
            'redirect_uri' => 'http://localhost/callback',
            'scopes' => ['openid', 'profile'],
            'response_type' => 'code',
            'state' => $this->state,
        ],
        'access_token' => [
            'grant_type' => 'authorization_code',
            'client_secret' => 'test',
            'redirect_uri' => 'http://localhost/callback',
        ],
        'refresh_token' => [
            'grant_type' => 'refresh_token',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ],
        'issuer' => 'https://provider.com'
    ]);
    $this->provider = 'my_provider';
    $this->service = new LaravelOpenIdConnect($this->provider);
});

it('throws InvalidProviderConfigurationException if provider configuration is missing', function () {
    Config::set('openid-connect.providers.my_provider', null);
    new LaravelOpenIdConnect('my_provider');
})->throws(InvalidProviderConfigurationException::class);

it('constructs with valid provider configuration', function () {
    $service = new LaravelOpenIdConnect($this->provider);
    expect($service)->toBeInstanceOf(LaravelOpenIdConnect::class);
});

it('generates the correct authorization URL', function () {
    $authUrl = $this->service->getAuthorizationUrl();

    $encodedRedirectUri = urlencode('http://localhost/callback');

    expect($authUrl)->toContain('https://provider.com/authorize')
        ->and($authUrl)->toContain('client_id=test_client_id')
        ->and($authUrl)->toContain('redirect_uri=' . $encodedRedirectUri)
        ->and($authUrl)->toContain('state=' . $this->state);
});

it('throws AuthenticationException if access token retrieval fails', function () {
    Http::fake([
        'https://provider.com/token' => Http::response(null, 401),
    ]);

    $this->service->getAccessToken('');
})->throws(ConnectionException::class);

it('retrieves access token successfully', function () {
    $mockToken = [
        'access_token' => 'token_value',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ];

    Http::fake([
        'https://provider.com/token' => Http::response([
            'access_token' => 'token_value',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
    ]);

    $accessToken = $this->service->getAccessToken('valid_code');

    expect($accessToken)->toBe($mockToken);
});

it('throws AuthenticationException if user info retrieval fails', function () {
    Http::fake([
        'https://provider.com/userinfo' => Http::response(null, 401),
    ]);

    $this->service->getUserInfo('invalid_token');
})->throws(ConnectionException::class);

it('retrieves user info successfully', function () {
    $mockUserInfo = [
        'sub' => '123456',
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ];

    Http::fake([
        'https://provider.com/userinfo' => Http::response($mockUserInfo, 200),
    ]);

    $userInfo = $this->service->getUserInfo('valid_token');

    expect($userInfo)->toBe($mockUserInfo);
});

it('throws AuthenticationException if refresh token fails', function () {
    Http::fake([
        'https://provider.com/token' => Http::response(null, 401),
    ]);

    $this->service->refreshToken('invalid_refresh_token');
})->throws(ConnectionException::class);

it('refreshes token successfully', function () {
    $mockRefreshedToken = [
        'access_token' => 'new_fake_token',
        'expires_in' => 3600,
    ];

    Http::fake([
        'https://provider.com/token' => Http::response($mockRefreshedToken, 200),
    ]);

    $refreshedToken = $this->service->refreshToken('valid_refresh_token');

    expect($refreshedToken)->toBe($mockRefreshedToken);
});
