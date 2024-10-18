<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectConfig;
use Illuminate\Support\Facades\Http;

covers(OpenIDConnectConfig::class);

beforeEach(function () {
    $this->config = [
        'provider_url' => 'https://provider.example.com',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'redirect_url' => 'https://redirect.example.com',
    ];
    $this->oidcConfig = new OpenIDConnectConfig($this->config);
});

it('can set and get client ID', function () {
    $this->oidcConfig->setClientID('new-client-id');
    expect($this->oidcConfig->getClientID())->toBe('new-client-id');
});

it('can set and get client secret', function () {
    $this->oidcConfig->setClientSecret('new-client-secret');
    expect($this->oidcConfig->getClientSecret())->toBe('new-client-secret');
});

it('throws exception for invalid redirect URL', function () {
    $this->oidcConfig->setRedirectURL('invalid-url');
})->throws(OpenIDConnectClientException::class, 'The redirect URL is not a valid URL');

it('can set and get redirect URL', function () {
    $this->oidcConfig->setRedirectURL('https://newredirect.example.com');
    expect($this->oidcConfig->getRedirectURL())->toBe('https://newredirect.example.com');
});

it('throws exception when getting invalid redirect URL', function () {
    $config = $this->config;
    $config['redirect_url'] = 'invalid-url';
    $oidcConfig = new OpenIDConnectConfig($config);
    $oidcConfig->getRedirectURL();
})->throws(OpenIDConnectClientException::class, 'The redirect URL is not a valid URL');

it('can set and get allowImplicitFlow', function () {
    $this->oidcConfig->setAllowImplicitFlow(true);
    expect($this->oidcConfig->getAllowImplicitFlow())->toBeTrue();
});

it('can set and get provider URL', function () {
    $this->oidcConfig->setProviderURL('https://newprovider.example.com');
    expect($this->oidcConfig->getProviderURL())->toBe('https://newprovider.example.com');
});

it('throws exception when provider URL is not set', function () {
    $config = $this->config;
    unset($config['provider_url']);
    $oidcConfig = new OpenIDConnectConfig($config);
    $oidcConfig->getProviderURL();
})->throws(OpenIDConnectClientException::class, 'The provider URL has not been set');

it('throws exception for invalid provider URL', function () {
    $this->oidcConfig->setProviderURL('invalid-url');
})->throws(OpenIDConnectClientException::class, 'The provider URL is not a valid URL');

it('can set and get encoding type', function () {
    $this->oidcConfig->setEncodingType(PHP_QUERY_RFC3986);
    expect($this->oidcConfig->getEncodingType())->toBe(PHP_QUERY_RFC3986);
});

it('can set and get leeway', function () {
    $this->oidcConfig->setLeeway(600);
    expect($this->oidcConfig->getLeeway())->toBe(600);
});

it('can set and get scope', function () {
    $this->oidcConfig->setScope(['email']);
    expect($this->oidcConfig->getScope())->toEqual(['openid', 'email']);
});

it('should return a default scope when none is provided', function () {
    expect($this->oidcConfig->getScope())->toEqual(['openid']);
});

it('throws exception when scope is not an array', function () {
    $this->oidcConfig->setScope('invalid-scope');
})->throws(
    TypeError::class,
    'CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectConfig::setScope(): Argument #1 ($scope) must be of type array, string given'
);

it('can set and get auth params', function () {
    $this->oidcConfig->setAuthParams(['key1' => 'value1']);
    expect($this->oidcConfig->getAuthParams())->toEqual(['key1' => 'value1']);
});

it('can add a single auth param', function () {
    $this->oidcConfig->addAuthParam('key2', 'value2');
    expect($this->oidcConfig->getAuthParams())->toEqual(['key2' => 'value2']);
});

it('throws exception if required configuration is missing', function () {
    $config = $this->config;
    unset($config['client_id']);
    new OpenIDConnectConfig($config);
})->throws(OpenIDConnectClientException::class, 'The client ID has not been set');

it('throws exception when fetching well-known config fails', function () {
    Http::fake([
        'https://provider.example.com/.well-known/openid-configuration' => Http::response(null, 404),
    ]);

    $this->oidcConfig->getWellKnownIssuer();
})->throws(OpenIDConnectClientException::class, 'Failed to fetch well-known configuration');

it('fetches well-known configuration successfully without parameters', function () {
    Http::fake([
        'https://provider.example.com/.well-known/openid-configuration' => Http::response([
            'issuer' => 'https://provider.example.com',
        ], 200),
    ]);

    $this->oidcConfig->getWellKnownIssuer();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://provider.example.com/.well-known/openid-configuration';
    });

    $wellKnownConfig = getPrivateProperty($this->oidcConfig, 'wellKnownConfig');
    expect($wellKnownConfig)->toEqual(['issuer' => 'https://provider.example.com']);
});

it('fetches well-known configuration successfully with query parameters', function () {
    $reflection = new ReflectionClass($this->oidcConfig);
    $property = $reflection->getProperty('wellKnownConfigParameters');
    $property->setAccessible(true);
    $property->setValue($this->oidcConfig, ['param' => 'value']);

    Http::fake([
        'https://provider.example.com/.well-known/openid-configuration?param=value' => Http::response([
            'issuer' => 'https://provider.example.com',
        ], 200),
    ]);

    $this->oidcConfig->getWellKnownIssuer();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://provider.example.com/.well-known/openid-configuration?param=value';
    });

    $wellKnownConfig = getPrivateProperty($this->oidcConfig, 'wellKnownConfig');
    expect($wellKnownConfig)->toEqual(['issuer' => 'https://provider.example.com']);
});

it('can add registration param', function () {
    $this->oidcConfig->addRegistrationParam(['key3' => 'value']);
    expect($this->oidcConfig->getRegistrationParams())->toEqual(['key3' => 'value']);
});

it('can retrieve provider config value', function () {
    Http::fake([
        'https://provider.example.com/.well-known/openid-configuration' => Http::response([
            'issuer' => 'https://provider.example.com',
            'key1' => 'value1',
        ], 200),
    ]);

    $value = $this->oidcConfig->getProviderConfigValue('key1');
    expect($value)->toBeString()
        ->and($value)
        ->toBe('value1');
});
