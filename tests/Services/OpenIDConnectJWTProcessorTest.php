<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectJWTProcessor;

it('can decode a JWT and return the correct section', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor();
    $jwt = base64_encode(json_encode(['alg' => 'HS256'])) . '.' . base64_encode(json_encode(['sub' => '1234567890'])) . '.' . base64_encode('signature');

    expect($jwtProcessor->decodeJWT($jwt, 0))->toBeObject()
        ->and($jwtProcessor->decodeJWT($jwt, 1))->toBeObject()
        ->and($jwtProcessor->decodeJWT($jwt, 2))->toBeNull();
});

it('throws exception when JWT header is missing parts', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor();

    $jwt = 'invalid.jwt';

    expect(fn () => $jwtProcessor->verifyJWTSignature($jwt, []))
        ->toThrow(OpenIDConnectClientException::class, 'JWT must have 3 parts: header, payload, and signature');
});

it('throws exception when signature is missing', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor();

    $jwt = base64_encode(json_encode(['alg' => 'HS256'])) . '.' . base64_encode(json_encode(['sub' => '1234567890']));

    expect(fn () => $jwtProcessor->verifyJWTSignature($jwt, []))
        ->toThrow(OpenIDConnectClientException::class, 'JWT must have 3 parts: header, payload, and signature');
});

it('throws exception when header algorithm is not supported', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor();

    $jwt = base64_encode(json_encode(['alg' => 'unsupported'])) . '.' . base64_encode(json_encode(['sub' => '1234567890'])) . '.' . base64_encode('signature');

    expect(fn () => $jwtProcessor->verifyJWTSignature($jwt, []))
        ->toThrow(OpenIDConnectClientException::class, 'No support for signature type: unsupported');
});

it('verifies HMAC signature correctly', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor('secret');

    $header = base64_encode(json_encode(['alg' => 'HS256']));
    $payload = base64_encode(json_encode(['sub' => '1234567890']));

    // The signature should be based on "header.payload"
    $signature = hash_hmac('sha256', $header . '.' . $payload, 'secret', true);

    // Build the complete JWT with base64-encoded signature
    $jwt = $header . '.' . $payload . '.' . base64_encode($signature);

    // Verify the JWT signature
    expect($jwtProcessor->verifyJWTSignature($jwt, []))->toBeTrue();
});

it('fails HMAC signature verification with wrong secret', function () {
    $jwtProcessor = new OpenIDConnectJWTProcessor('wrong_secret');

    $payload = base64_encode(json_encode(['sub' => '1234567890']));
    $signature = hash_hmac('sha256', $payload, 'secret', true);
    $jwt = base64_encode(json_encode(['alg' => 'HS256'])) . '.' . $payload . '.' . base64_encode($signature);

    expect($jwtProcessor->verifyJWTSignature($jwt, []))->toBeFalse();
});