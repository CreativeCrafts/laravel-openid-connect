<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectManager;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectJWTProcessor;
use Illuminate\Support\Facades\Http;

function makeHs256Jwt(string $secret, array $payload, array $header = ['alg' => 'HS256', 'typ' => 'JWT']): string
{
    $b64 = static function (string $json): string {
        $enc = base64_encode($json);
        return rtrim(strtr($enc, '+/', '-_'), '=');
    };
    $headerB64 = $b64(json_encode($header, JSON_THROW_ON_ERROR));
    $payloadB64 = $b64(json_encode($payload, JSON_THROW_ON_ERROR));
    $data = $headerB64 . '.' . $payloadB64;
    $sig = hash_hmac('sha256', $data, $secret, true);
    $sigB64 = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    return $data . '.' . $sigB64;
}

it('completes authorization code flow and clears bundle with tombstone', function () {
    // Arrange provider config (provide endpoints inline to avoid well-known fetch except issuer)
    $issuer = 'https://issuer.test';
    $providerConfig = [
        'provider_url' => 'https://provider.test',
        'client_id' => 'client-123',
        'client_secret' => 'secret-xyz',
        'redirect_url' => 'https://app.test/callback',
        'scopes' => ['openid', 'profile'],
        'authorization_endpoint' => 'https://op.test/authorize',
        'token_endpoint' => 'https://op.test/token',
        'jwks_uri' => 'https://op.test/jwks',
    ];

    // Fake well-known configuration with issuer
    Http::fake([
        'https://provider.test/.well-known/openid-configuration' => Http::response([
            'issuer' => $issuer,
            'authorization_endpoint' => $providerConfig['authorization_endpoint'],
            'token_endpoint' => $providerConfig['token_endpoint'],
            'jwks_uri' => $providerConfig['jwks_uri'],
            'code_challenge_methods_supported' => ['plain'],
        ], 200, ['Content-Type' => 'application/json']),
    ]);

    $manager = new OpenIDConnectManager($providerConfig);

    // Inject JWT processor using HS256 with known secret
    $manager->setJwtProcessor(new OpenIDConnectJWTProcessor($providerConfig['client_secret']));

    // Use a fresh token manager and pre-create a state bundle (simulating pre-redirect step)
    $tokenManager = new OpenIDConnectTokenManager();
    $manager->setTokenManager($tokenManager);

    $state = 'state123';
    $nonce = 'nonce123';
    $codeVerifier = 'verifier123';
    $tokenManager->saveStateBundle($state, $nonce, $codeVerifier);

    // Prepare a synthetic token endpoint response with a valid HS256 id_token
    $now = time();
    $idToken = makeHs256Jwt($providerConfig['client_secret'], [
        'iss' => $issuer,
        'aud' => $providerConfig['client_id'],
        'sub' => 'user-1',
        'exp' => $now + 3600,
        'iat' => $now,
        'nonce' => $nonce,
    ]);

    Http::fake([ // augment existing fakes
        $providerConfig['token_endpoint'] => Http::response([
            'access_token' => 'access-abc',
            'id_token' => $idToken,
            'refresh_token' => 'refresh-xyz',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200, ['Content-Type' => 'application/json']),
        $providerConfig['jwks_uri'] => Http::response(['keys' => []], 200, ['Content-Type' => 'application/json']),
    ]);

    // Simulate callback request
    $_REQUEST['state'] = $state;
    $_REQUEST['code'] = 'code-abc';

    // Act
    $result = $manager->authenticate();

    // Assert success
    expect($result)->toBeTrue();

    // The bundle should now be cleared and tombstoned (load should return null)
    expect($tokenManager->loadStateBundle($state))->toBeNull();
});

it('completes implicit flow validating nonce and cleans up', function () {
    $issuer = 'https://issuer.test';
    $providerConfig = [
        'provider_url' => 'https://provider.test',
        'client_id' => 'client-123',
        'client_secret' => 'secret-xyz',
        'redirect_url' => 'https://app.test/callback',
        'scopes' => ['openid'],
        'authorization_endpoint' => 'https://op.test/authorize',
        'token_endpoint' => 'https://op.test/token',
        'jwks_uri' => 'https://op.test/jwks',
    ];

    // Fake well-known
    Http::fake([
        'https://provider.test/.well-known/openid-configuration' => Http::response([
            'issuer' => $issuer,
            'authorization_endpoint' => $providerConfig['authorization_endpoint'],
            'token_endpoint' => $providerConfig['token_endpoint'],
            'jwks_uri' => $providerConfig['jwks_uri'],
        ], 200, ['Content-Type' => 'application/json']),
        $providerConfig['jwks_uri'] => Http::response(['keys' => []], 200, ['Content-Type' => 'application/json']),
    ]);

    $manager = new OpenIDConnectManager($providerConfig);
    // allow implicit
    $configProp = new ReflectionProperty($manager, 'config');
    $configProp->setAccessible(true);
    $cfg = $configProp->getValue($manager);
    $cfg->setAllowImplicitFlow(true);

    $manager->setJwtProcessor(new OpenIDConnectJWTProcessor($providerConfig['client_secret']));

    $tokenManager = new OpenIDConnectTokenManager();
    $manager->setTokenManager($tokenManager);

    $state = 'state-imp';
    $nonce = 'nonce-imp';
    $tokenManager->saveStateBundle($state, $nonce, null);

    // Construct HS256 id_token with correct nonce
    $now = time();
    $idToken = makeHs256Jwt($providerConfig['client_secret'], [
        'iss' => $issuer,
        'aud' => $providerConfig['client_id'],
        'sub' => 'user-imp',
        'exp' => $now + 3600,
        'iat' => $now,
        'nonce' => $nonce,
    ]);

    // Simulate front-channel response
    $_REQUEST = [
        'state' => $state,
        'id_token' => $idToken,
        'access_token' => 'acc-1',
    ];

    $result = $manager->authenticate();
    expect($result)->toBeTrue();
    expect($tokenManager->loadStateBundle($state))->toBeNull();
});
