<?php

declare(strict_types=1);

use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectTokenManager;

covers(OpenIDConnectTokenManager::class);

beforeEach(function () {
    $this->tokenManager = new OpenIDConnectTokenManager();
});

it('sets and retrieves the access token', function () {
    $accessToken = 'sample_access_token';
    $this->tokenManager->setAccessToken($accessToken);
    expect($this->tokenManager->getAccessToken())->toBe($accessToken);
});

it('sets and retrieves the refresh token', function () {
    $refreshToken = 'sample_refresh_token';
    $this->tokenManager->setRefreshToken($refreshToken);
    expect($this->tokenManager->getRefreshToken())->toBe($refreshToken);
});

it('clears the refresh token', function () {
    $this->tokenManager->setRefreshToken(null);
    expect($this->tokenManager->getRefreshToken())->toBeNull();
});

it('sets and retrieves the ID token', function () {
    $idToken = 'sample_id_token';
    $this->tokenManager->setIdToken($idToken);

    expect($this->tokenManager->getIdToken())->toBe($idToken);
});

it('sets and retrieves the token response', function () {
    $response = [
        'access_token' => 'sample_access_token',
        'refresh_token' => 'sample_refresh_token',
        'id_token' => 'sample_id_token',
    ];
    $this->tokenManager->setTokenResponse($response);

    expect($this->tokenManager->getTokenResponse())->toBe($response);
});

it('commits the session', function () {
    $this->tokenManager->commitSession();

    expect(session_status())->toBe(PHP_SESSION_NONE);
});

it('sets and retrieves a session key', function () {
    $key = 'test_key';
    $value = 'test_value';
    $this->tokenManager->setSessionKey($key, $value);

    expect($this->tokenManager->getSessionKey($key))->toBe($value);
});

it('retrieves a non-existing session key', function () {
    $key = 'non_existing_key';

    expect($this->tokenManager->getSessionKey($key))->toBeNull();
});

it('unsets a session key', function () {
    $key = 'test_key';
    $value = 'test_value';
    $this->tokenManager->setSessionKey($key, $value);
    $this->tokenManager->unsetSessionKey($key);

    expect($this->tokenManager->getSessionKey($key))->toBeNull();
});

it('sets and retrieves nonce value', function () {
    $nonce = 'sample_nonce';
    $this->tokenManager->setNonce($nonce);

    expect($this->tokenManager->getNonce())->toBe($nonce);
});

it('unsets the nonce value', function () {
    $this->tokenManager->unsetNonce();

    expect($this->tokenManager->getNonce())->toBeNull();
});

it('sets and retrieves state value', function () {
    $state = 'sample_state';
    $this->tokenManager->setState($state);

    expect($this->tokenManager->getState())->toBe($state);
});

it('unsets the state value', function () {
    $this->tokenManager->unsetState();

    expect($this->tokenManager->getState())->toBeNull();
});

it('sets and retrieves code verifier', function () {
    $codeVerifier = 'sample_code_verifier';
    $this->tokenManager->setCodeVerifier($codeVerifier);

    expect($this->tokenManager->getCodeVerifier())->toBe($codeVerifier);
});

it('unsets the code verifier', function () {
    $this->tokenManager->unsetCodeVerifier();

    expect($this->tokenManager->getCodeVerifier())->toBeNull();
});

it('generates a random string', function () {
    $randString = $this->tokenManager->generateRandString(16);

    expect(strlen($randString))->toBe(32); // bin2hex converts 16 bytes into a 32-character string
});