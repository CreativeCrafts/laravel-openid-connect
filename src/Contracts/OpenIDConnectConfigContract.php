<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

/**
 * Interface for OpenID Connect configuration settings.
 */
interface OpenIDConnectConfigContract
{
    /**
     * Constructs the OpenIDConnectConfigContract with optional initial configuration.
     *
     * @param array $config The initial configuration settings.
     */
    public function __construct(array $config = []);

    /**
     * Retrieves the value indicating whether implicit flow is allowed.
     *
     * @return bool True if implicit flow is allowed, false otherwise.
     */
    public function getAllowImplicitFlow(): bool;

    /**
     * Sets the value indicating whether implicit flow is allowed.
     *
     * @param bool $allow True to allow implicit flow, false otherwise.
     */
    public function setAllowImplicitFlow(bool $allow): void;

    /**
     * Adds registration parameters to the OpenID Connect configuration.
     *
     * This method merges the provided array of registration parameters with the existing ones.
     * The registration parameters are used when registering a new client with the OpenID Connect provider.
     *
     * @param array $param The registration parameters to be added.
     */
    public function addRegistrationParam(array $param): void;

    /**
     * Sets the client ID for the OpenID Connect provider.
     *
     * @param string $clientID The client ID.
     */
    public function setClientID(string $clientID): void;

    /**
     * Retrieves the client ID for the OpenID Connect provider.
     *
     * @return string|null The client ID or null if not set.
     */
    public function getClientID(): ?string;

    /**
     * Sets the client secret for the OpenID Connect provider.
     *
     * @param string $clientSecret The client secret.
     */
    public function setClientSecret(string $clientSecret): void;

    /**
     * Retrieves the client secret for the OpenID Connect provider.
     *
     * @return string|null The client secret or null if not set.
     */
    public function getClientSecret(): ?string;

    /**
     * Sets the redirect URL for the OpenID Connect provider.
     *
     * @param string $url The redirect URL.
     */
    public function setRedirectURL(string $url): void;

    /**
     * Retrieves the redirect URL for the OpenID Connect provider.
     *
     * @return string|null The redirect URL or null if not set.
     */
    public function getRedirectURL(): ?string;

    /**
     * Sets the provider URL for the OpenID Connect provider.
     *
     * This method validates the provided URL and sets it as the provider URL.
     * If the provider URL is not already set in the internal configuration, it is added.
     *
     * @param string $providerUrl The URL of the OpenID Connect provider.
     */
    public function setProviderURL(string $providerUrl): void;

    /**
     * Retrieves a specific provider configuration value.
     *
     * @param string $key The key of the configuration value.
     * @param string|null $default The default value to return if the key is not found.
     * @return string|array The configuration value or the default value if not found.
     */
    public function getProviderConfigValue(string $key, string $default = null): string|array;

    /**
     * Sets the encoding type for the OpenID Connect provider.
     *
     * @param int $encType The encoding type.
     */
    public function setEncodingType(int $encType): void;

    /**
     * Retrieves the encoding type for the OpenID Connect provider.
     *
     * @return int The encoding type.
     */
    public function getEncodingType(): int;

    /**
     * Retrieves the leeway value for the OpenID Connect provider.
     *
     * @return int The leeway value.
     */
    public function getLeeway(): int;

    /**
     * Sets the leeway value for the OpenID Connect provider.
     *
     * @param int $leeway The leeway value.
     */
    public function setLeeway(int $leeway): void;

    /**
     * Retrieves the authentication parameters for the OpenID Connect provider.
     *
     * @return array The authentication parameters.
     */
    public function getAuthParams(): array;

    /**
     * Sets the authentication parameters for the OpenID Connect provider.
     *
     * @param array $params The authentication parameters.
     */
    public function setAuthParams(array $params): void;

    /**
     * Adds an authentication parameter for the OpenID Connect provider.
     *
     * @param string $key The key of the authentication parameter.
     * @param string $value The value of the authentication parameter.
     */
    public function addAuthParam(string $key, string $value): void;

    /**
     * Sets the scope for the OpenID Connect provider.
     *
     * This method merges the provided array of scopes with the existing ones.
     * The scope determines the level of access requested by the client to the user's information.
     *
     * @param array $scope The array of scopes to be added. Each scope represents a specific type of access.
     */
    public function setScope(array $scope): void;

    /**
     * Retrieves the scope for the OpenID Connect provider.
     *
     * @return array The scope.
     */
    public function getScope(): array;

    /**
     * Retrieves the well-known issuer URL from the OpenID Connect provider's configuration.
     *
     * The well-known issuer URL is the URL that identifies the OpenID Connect provider.
     * By default, the function appends a trailing slash to the issuer URL.
     * If the optional parameter $appendSlash is set to false, the trailing slash is not appended.
     *
     * @param bool $appendSlash (optional) Indicates whether to append a trailing slash to the issuer URL. Default is true.
     *
     * @return string The well-known issuer URL.
     */
    public function getWellKnownIssuer(bool $appendSlash = false): string;

    /**
     * Retrieves the provider URL for the OpenID Connect provider.
     *
     * This method checks if the provider URL has been set in the internal configuration.
     * If the provider URL is not set, it throws an OpenIDConnectClientException with an appropriate error message.
     *
     * @return string The provider URL.
     */
    public function getProviderURL(): string;

    /**
     * Retrieves the registration parameters for the OpenID Connect provider.
     *
     * @return array The registration parameters.
     */
    public function getRegistrationParams(): array;
}
