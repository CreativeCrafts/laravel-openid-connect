<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectManager;

it('throws an exception if there is an error in the request', function () {
    $config = ['allow_implicit_flow' => true]; // Example config
    $manager = new OpenIDConnectManager($config);

    $_REQUEST['error'] = 'access_denied';

    $manager->authenticate();
})->throws(OpenIDConnectClientException::class, 'The provider URL has not been set');

/* it('handles authorization code flow', function () {
    // Step 1: Create a configuration array
    $configArray = [
        'provider_url' => 'https://example.com',
        'issuer' => 'https://example.com',
        'client_id' => 'example_client_id',
        'client_secret' => 'example_client_secret',
        'scopes' => ['openid', 'email', 'profile'],
        'response_type' => 'code',
        'redirect_url' => 'https://example.com/callback',
    ];

    // Step 2: Create real instances of the final classes
    $config = new OpenIDConnectConfig($configArray);
    $httpClient = new OpenIDConnectHttpClient();
    $tokenManager = new OpenIDConnectTokenManager();
    $jwtProcessor = new OpenIDConnectJWTProcessor();

    // Simulate request parameters
    $_REQUEST['code'] = 'mock_code';
    $_REQUEST['state'] = 'mock_state';

    // Step 3: Initialize the OpenIDConnectManager
    $manager = new OpenIDConnectManager($configArray);

    // Set dependencies using the setter methods
    $manager->setHttpClient($httpClient);
    $manager->setTokenManager($tokenManager);
    $manager->setJwtProcessor($jwtProcessor);

    // Step 4: Assert the result of authentication
    expect($manager->authenticate())->toBeTrue();
});*/
