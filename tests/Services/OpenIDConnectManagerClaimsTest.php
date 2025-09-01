<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectHttpClient;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectManager;
use Illuminate\Support\Facades\Http;

function makeManagerForClaims(): OpenIDConnectManager
{
    $config = [
        'provider_url' => 'https://provider.example.com',
        'client_id' => 'client-123',
        'client_secret' => 'secret-xyz',
        'redirect_url' => 'https://app.example.com/callback',
        'scopes' => ['openid', 'profile'],
        'jwks_uri' => 'https://provider.example.com/jwks',
    ];

    $manager = new OpenIDConnectManager($config);

    // Set wellKnownConfig directly to avoid network calls
    $refConfig = new ReflectionClass(getPrivateProperty($manager, 'config'));
    $prop = $refConfig->getProperty('wellKnownConfig');
    $prop->setAccessible(true);
    $prop->setValue(getPrivateProperty($manager, 'config'), [
        'issuer' => 'https://issuer.example.com',
    ]);

    return $manager;
}

it('gets JWKS via GET without calling well-known', function () {
    $manager = makeManagerForClaims();

    Http::fake([
        'https://provider.example.com/jwks' => Http::response([
            'keys' => [
                ['kty' => 'RSA', 'kid' => '1', 'e' => 'AQAB', 'n' => 'AQAB'],
            ],
        ], 200, ['Content-Type' => 'application/json']),
    ]);

    $manager->setHttpClient(new OpenIDConnectHttpClient());

    $ref = new ReflectionClass($manager);
    $m = $ref->getMethod('getJwks');
    $m->setAccessible(true);

    $keys = $m->invoke($manager);
    expect($keys)->toBeArray()->and($keys)->toHaveCount(1)->and($keys[0]['kid'])->toBe('1');
});

it('verifies JWT claims successfully (issuer, audience, sub, exp/nbf, nonce, at_hash)', function () {
    $manager = makeManagerForClaims();

    // Prepare TokenManager state: nonce and id_token (to infer alg RS256)
    $refManager = new ReflectionClass($manager);
    $tmProp = $refManager->getProperty('tokenManager');
    $tmProp->setAccessible(true);
    $tokenManager = $tmProp->getValue($manager);
    $tokenManager->setNonce('abc123');

    // Minimal header with alg RS256 to influence at_hash computation length
    $header = base64_encode(json_encode(['alg' => 'RS256'], JSON_THROW_ON_ERROR));
    $payload = base64_encode(json_encode(['sub' => 'user'], JSON_THROW_ON_ERROR));
    $tokenManager->setIdToken($header . '.' . $payload . '.sig');

    $accessToken = 'access_token_value';

    // Compute expected at_hash per validateAccessTokenHash logic
    $hash = hash('sha256', $accessToken, true);
    $half = substr($hash, 0, 16);
    $expectedAtHash = (new ReflectionClass($manager))->getProperty('jwtProcessor');
    $expectedAtHash->setAccessible(true);
    /** @var CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectJWTProcessor $jwt */
    $jwt = $expectedAtHash->getValue($manager);
    $atHash = $jwt->urlEncode($half);

    // Build claims object
    $now = time();
    $claims = (object) [
        'iss' => 'https://issuer.example.com',
        'aud' => ['client-123'],
        'sub' => 'user-1',
        'exp' => $now + 300,
        'nbf' => $now - 10,
        'nonce' => 'abc123',
        'at_hash' => $atHash,
    ];

    $method = $refManager->getMethod('verifyJWTClaims');
    $method->setAccessible(true);

    $ok = $method->invoke($manager, $claims, $accessToken);
    expect($ok)->toBeTrue();
});

it('fails JWT claims verification when audience does not include client_id', function () {
    $manager = makeManagerForClaims();

    $refManager = new ReflectionClass($manager);
    $method = $refManager->getMethod('verifyJWTClaims');
    $method->setAccessible(true);

    $claims = (object) [
        'iss' => 'https://issuer.example.com',
        'aud' => ['some-other-client'],
        'sub' => 'user-1',
    ];

    $ok = $method->invoke($manager, $claims, 'token');
    expect($ok)->toBeFalse();
});
