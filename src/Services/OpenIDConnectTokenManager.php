<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Services;

use CreativeCrafts\LaravelOpenidConnect\Contracts\OpenIDConnectTokenManagerContract;
use CreativeCrafts\LaravelOpenidConnect\Contracts\TokenStorageContract;
use CreativeCrafts\LaravelOpenidConnect\Storage\CacheTokenStorage;
use CreativeCrafts\LaravelOpenidConnect\Storage\NullTokenStorage;
use CreativeCrafts\LaravelOpenidConnect\Storage\SessionTokenStorage;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use JsonException;
use Psr\SimpleCache\InvalidArgumentException;

final class OpenIDConnectTokenManager implements OpenIDConnectTokenManagerContract
{
    private const STATE_BUNDLE_PREFIX = 'oidc:state:';
    private TokenStorageContract $storage;

    // @pest-mutate-ignore
    private ?string $accessToken = null;

    // @pest-mutate-ignore
    private ?string $refreshToken = null;

    // @pest-mutate-ignore
    private ?string $idToken = null;

    // @pest-mutate-ignore
    private ?array $tokenResponse = null;

    /**
     * Initialises the OpenID Connect Token Manager with the specified storage implementation.
     * This constructor sets up the token storage mechanism for the OpenID Connect session management.
     * If no storage is provided, it automatically resolves the storage type from Laravel configuration
     * or falls back to sensible defaults. Supported storage types include cache, session, and null (stateless).
     *
     * @param TokenStorageContract|null $storage Optional token storage implementation. If null, the storage
     *                                          will be automatically resolved from Laravel configuration.
     *                                          Defaults to session storage if Laravel is available,
     *                                          otherwise falls back to null storage (stateless).
     * @throws BindingResolutionException If there's an issue resolving dependencies from the Laravel container.
     */
    public function __construct(?TokenStorageContract $storage = null)
    {
        if ($storage instanceof TokenStorageContract) {
            $this->storage = $storage;
            return;
        }

        // Resolve storage from Laravel config if available, else default to stateless (null storage)
        $storageRaw = function_exists('config') ? Config::string(key: 'openid-connect.storage') : null;
        $storageDriver = is_string($storageRaw) ? $storageRaw : 'session';
        $prefixRaw = function_exists('config') ? Config::string(key: 'openid-connect.session_key_prefix') : null;
        $prefix = is_string($prefixRaw) ? $prefixRaw : 'openid_connect_';

        if ($storageDriver === 'cache') {
            $instance = class_exists(Container::class) ? Container::getInstance() : null;
            if ($instance instanceof Container) {
                /** @var Factory $cacheFactory */
                $cacheFactory = $instance->make('cache');
                $storeNameRaw = function_exists('config') ? Config::string(key: 'openid-connect.cache_store') : null;
                $storeName = is_string($storeNameRaw) ? $storeNameRaw : null;
                $cacheRepo = $storeName !== null && $storeName !== '' && $storeName !== '0' ? $cacheFactory->store($storeName) : $instance->make('cache.store');
                $rawTtl = function_exists('config') ? Config::integer(key: 'openid-connect.cache_ttl', default: 300) : null;
                $ttl = is_int($rawTtl) ? $rawTtl : null;
                $this->storage = new CacheTokenStorage($cacheRepo, $prefix, $ttl);
                return;
            }
        }

        if ($storageDriver === 'session') {
            $instance = class_exists(Container::class) ? Container::getInstance() : null;
            if ($instance instanceof Container && $instance->bound('session')) {
                $session = $instance->make('session');
                $this->storage = new SessionTokenStorage($session, $prefix);
                return;
            }
        }

        // Fallback to null storage (stateless)
        $this->storage = new NullTokenStorage();
    }

    /**
     * Sets the access token for the OpenID Connect session.
     *
     * @param string $accessToken The access token to be set.
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Retrieves the access token for the OpenID Connect session.
     *
     * @return string|null The access token, or null if it is not set.
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Sets the refresh token for the OpenID Connect session.
     *
     * @param string|null $refreshToken The refresh token to be set. If null, the refresh token will be cleared.
     *
     */
    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * Retrieves the refresh token for the OpenID Connect session.
     *
     * @return string|null The refresh token, or null if it is not set.
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Sets the ID token for the OpenID Connect session.
     *
     * @param string $idToken The ID token to be set. This token is used to authenticate the user and provide user information.
     */
    public function setIdToken(string $idToken): void
    {
        $this->idToken = $idToken;
    }

    /**
     * Retrieves the ID token for the OpenID Connect session.
     *
     * The ID token is used to authenticate the user and provide user information.
     *
     * @return string|null The ID token, or null if it is not set.
     */
    public function getIdToken(): ?string
    {
        return $this->idToken;
    }

    /**
     * Sets the token response received from the OpenID Connect server.
     * This method is used to store the token response received from the server. The token response
     * typically contains access, refresh, and ID tokens, along with other relevant information.
     *
     * @param array $response The token response received from the server.
     */
    public function setTokenResponse(array $response): void
    {
        $this->tokenResponse = $response;
    }

    /**
     * Retrieves the token response received from the OpenID Connect server.
     *
     * This method returns the token response array that was set using the `setTokenResponse` method.
     * The token response typically contains access, refresh, and ID tokens, along with other relevant information.
     *
     * @return array|null The token response array, or null if it is not set.
     */
    public function getTokenResponse(): ?array
    {
        return $this->tokenResponse;
    }

    /**
     * Commits the current session and closes the session.
     *
     * This method is used to commit the current session data and close the session. It ensures that
     * all session data is saved and that the session is ready for the next request.
     */
    public function commitSession(): void
    {
        $this->storage->commit();
    }

    /**
     * Sets a session key-value pair.
     *
     * This method is used to store a key-value pair in the PHP session. It ensures that the session is started
     * before setting the key-value pair.
     *
     * @param string $key The key to be used for storing the value in the session.
     * @param string $value The value to be stored in the session.
     */
    public function setSessionKey(string $key, string $value): void
    {
        $this->storage->put($key, $value);
    }

    /**
     * Retrieves a session key-value pair.
     * This method retrieves a value from the PHP session using the provided key.
     * If the key exists and its value is not empty, the method returns the value.
     * Otherwise, it returns null.
     *
     * @param string $key The key to be used for retrieving the value from the session.
     * @return string|null The value associated with the given key in the session, or null if the key does not exist or its value is empty.
     * @throws InvalidArgumentException
     */
    public function getSessionKey(string $key): ?string
    {
        return $this->storage->get($key);
    }

    /**
     * Unsets a session key-value pair.
     *
     * This method removes a specific key-value pair from the PHP session.
     * It ensures that the session is started before attempting to unset the key.
     *
     * @param string $key The key to be unset from the session.
     */
    public function unsetSessionKey(string $key): void
    {
        $this->storage->forget($key);
    }

    /**
     * Sets the nonce value for the OpenID Connect session.
     *
     * The nonce is a random string that is used to prevent replay attacks. It is generated by the
     * `generateRandString` method and stored in the PHP session.
     *
     * @param string $nonce The nonce value to be set.
     */
    public function setNonce(string $nonce): void
    {
        $this->setSessionKey('nonce', $nonce);
    }

    /**
     * Retrieves the nonce value for the OpenID Connect session.
     * The nonce is a random string used to prevent replay attacks. It is generated by the
     * `generateRandString` method and stored in the PHP session.
     *
     * @return string|null The nonce value stored in the session, or null if it is not set.
     * @throws InvalidArgumentException
     */
    public function getNonce(): ?string
    {
        return $this->getSessionKey('nonce');
    }

    /**
     * Unsets the nonce value for the OpenID Connect session.
     *
     * The nonce is a random string that is used to prevent replay attacks. It is stored in the PHP session.
     * This method removes the nonce value from the session by calling the `unsetSessionKey` method with the
     * 'openid_connect_nonce' key.
     */
    public function unsetNonce(): void
    {
        $this->unsetSessionKey('nonce');
    }

    /**
     * Sets the state value for the OpenID Connect session.
     *
     * The state value is used to maintain the state between the client and the server during the authorization process.
     * It is generated by the `generateRandString` method and stored in the PHP session.
     *
     * @param string $state The state value to be set.
     */
    public function setState(string $state): void
    {
        $this->setSessionKey('state', $state);
    }

    /**
     * Retrieves the state value for the OpenID Connect session.
     * The state value is used to maintain the state between the client and the server during the authorization process.
     * It is generated by the `generateRandString` method and stored in the PHP session.
     *
     * @return string|null The state value stored in the session, or null if it is not set.
     * @throws InvalidArgumentException
     */
    public function getState(): ?string
    {
        return $this->getSessionKey('state');
    }

    /**
     * Unsets the state value for the OpenID Connect session.
     *
     * The state value is used to maintain the state between the client and the server during the authorization process.
     * It is stored in the PHP session. This method removes the state value from the session by calling the
     * `unsetSessionKey` method with the 'openid_connect_state' key.
     */
    public function unsetState(): void
    {
        $this->unsetSessionKey('state');
    }

    /**
     * Sets the code verifier for the OpenID Connect session.
     *
     * The code verifier is a random string used in the authorization code flow to prevent CSRF attacks.
     * It is generated by the `generateRandString` method and stored in the PHP session.
     *
     * @param string $codeVerifier The code verifier to be set.
     */
    public function setCodeVerifier(string $codeVerifier): void
    {
        $this->setSessionKey('code_verifier', $codeVerifier);
    }

    /**
     * Retrieves the code verifier for the OpenID Connect session.
     * The code verifier is a random string used in the authorization code flow to prevent CSRF attacks.
     * It is generated by the `generateRandString` method and stored in the PHP session.
     *
     * @return string|null The code verifier stored in the session, or null if it is not set.
     * @throws InvalidArgumentException
     */
    public function getCodeVerifier(): ?string
    {
        return $this->getSessionKey('code_verifier');
    }

    /**
     * Unsets the code verifier for the OpenID Connect session.
     *
     * The code verifier is a random string used in the authorization code flow to prevent CSRF attacks.
     * It is stored in the PHP session. This method removes the code verifier value from the session by calling the
     * `unsetSessionKey` method with the 'openid_connect_code_verifier' key.
     */
    public function unsetCodeVerifier(): void
    {
        $this->unsetSessionKey('code_verifier');
    }

    /**
     * Generates a random string of 32 characters using the bin hex function and random_bytes function.
     *
     * @param int<1, max> $randomNumber The number of random bytes to generate. Default is 16.
     * @return string A random string of 32 characters.
     * @throws Exception If the random_bytes function fails to generate the required number of bytes.
     */
    public function generateRandString(int $randomNumber = 16): string
    {
        return bin2hex(random_bytes($randomNumber));
    }

    /**
     * Save a transient bundle scoped by state containing nonce and optional code_verifier.
     *
     * @throws JsonException
     */
    public function saveStateBundle(string $state, string $nonce, ?string $codeVerifier = null): void
    {
        $payload = json_encode([
            'nonce' => $nonce,
            'code_verifier' => $codeVerifier,
        ], JSON_THROW_ON_ERROR);
        $this->storage->put(self::STATE_BUNDLE_PREFIX . $state, $payload);
    }

    /**
     * Load a state-scoped transient bundle (nonce, code_verifier).
     *
     * @return array|null :?string}|null
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function loadStateBundle(string $state): ?array
    {
        $raw = $this->storage->get(self::STATE_BUNDLE_PREFIX . $state);
        if (!is_string($raw)) {
            return null;
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['nonce'])) {
            return null;
        }
        return [
            'nonce' => fluent($data)->string(key: 'nonce')->value(),
            'code_verifier' => array_key_exists('code_verifier', $data) && $data['code_verifier'] !== null ? (string)$data['code_verifier'] : null,
        ];
    }

    /**
     * Clear the state-scoped bundle after it has been used.
     */
    public function clearStateBundle(string $state): void
    {
        $this->storage->forget(self::STATE_BUNDLE_PREFIX . $state);
    }
}
