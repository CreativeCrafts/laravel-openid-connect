<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenIdConnect\Contracts;

interface OpenIDConnectTokenManagerContract
{
    /**
     * Sets the access token.
     *
     * @param string $accessToken The access token to be set.
     */
    public function setAccessToken(string $accessToken): void;

    /**
     * Retrieves the access token.
     *
     * @return string|null The current access token or null if not set.
     */
    public function getAccessToken(): ?string;

    /**
     * Sets the refresh token.
     *
     * @param string|null $refreshToken The refresh token to be set. If null, the refresh token will be unset.
     */
    public function setRefreshToken(?string $refreshToken): void;

    /**
     * Retrieves the refresh token.
     *
     * @return string|null The current refresh token or null if not set.
     */
    public function getRefreshToken(): ?string;

    /**
     * Sets the ID token.
     *
     * @param string $idToken The ID token to be set.
     */
    public function setIdToken(string $idToken): void;

    /**
     * Retrieves the ID token.
     *
     * @return string|null The current ID token or null if not set.
     */
    public function getIdToken(): ?string;

    /**
     * Sets the token response.
     *
     * @param array $response The token response to be set.
     */
    public function setTokenResponse(array $response): void;

    /**
     * Retrieves the token response.
     *
     * @return array|null The current token response or null if not set.
     */
    public function getTokenResponse(): ?array;

    /**
     * Commits the session.
     */
    public function commitSession(): void;

    /**
     * Sets a session key-value pair.
     *
     * @param string $key The key of the session data.
     * @param string $value The value of the session data.
     */
    public function setSessionKey(string $key, string $value): void;

    /**
     * Retrieves a session value by its key.
     *
     * @param string $key The key of the session data.
     * @return string|null The value of the session data or null if not set.
     */
    public function getSessionKey(string $key): ?string;

    /**
     * Unsets a session key.
     *
     * @param string $key The key of the session data to be unset.
     */
    public function unsetSessionKey(string $key): void;

    /**
     * Sets the nonce.
     *
     * @param string $nonce The nonce to be set.
     */
    public function setNonce(string $nonce): void;

    /**
     * Retrieves the nonce.
     *
     * @return string|null The current nonce or null if not set.
     */
    public function getNonce(): ?string;

    /**
     * Unsets the nonce.
     */
    public function unsetNonce(): void;

    /**
     * Sets the state.
     *
     * @param string $state The state to be set.
     */
    public function setState(string $state): void;

    /**
     * Retrieves the state.
     *
     * @return string|null The current state or null if not set.
     */
    public function getState(): ?string;

    /**
     * Unsets the state.
     */
    public function unsetState(): void;

    /**
     * Sets the code verifier.
     *
     * @param string $codeVerifier The code verifier to be set.
     */
    public function setCodeVerifier(string $codeVerifier): void;

    /**
     * Retrieves the code verifier.
     *
     * @return string|null The current code verifier or null if not set.
     */
    public function getCodeVerifier(): ?string;

    /**
     * Unsets the code verifier.
     */
    public function unsetCodeVerifier(): void;

    /**
     * Generates a random string of a specified length.
     *
     * @param int $randomNumber The length of the random string. Default is 16.
     * @return string The generated random string.
     */
    public function generateRandString(int $randomNumber = 16): string;
}
