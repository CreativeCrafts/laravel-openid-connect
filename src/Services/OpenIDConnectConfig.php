<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Services;

use CreativeCrafts\LaravelOpenidConnect\Contracts\OpenIDConnectConfigContract;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use Exception;
use Illuminate\Support\Facades\Http;

final class OpenIDConnectConfig implements OpenIDConnectConfigContract
{
    private string $clientID;

    private string $clientSecret;

    private array $providerConfig;

    // @pest-mutate-ignore
    private ?string $redirectURL = null;

    // @pest-mutate-ignore
    private int $encType = PHP_QUERY_RFC1738;

    // @pest-mutate-ignore
    private int $leeway = 300; // Default leeway in seconds

    // @pest-mutate-ignore
    private array $authParams = [];

    // @pest-mutate-ignore
    private bool $allowImplicitFlow = false;

    // @pest-mutate-ignore
    private ?array $wellKnownConfig = null;

    // @pest-mutate-ignore
    private array $wellKnownConfigParameters = [];

    // @pest-mutate-ignore
    private array $scopes = [];

    // @pest-mutate-ignore
    private array $registrationParams = [];

    /**
     * Constructs the OpenIDConnectConfigContract with optional initial configuration.
     *
     * @param array $config The initial configuration settings.
     * @throws OpenIDConnectClientException
     */
    public function __construct(
        protected array $config = []
    ) {
        $this->setUpConfig($config);
    }

    /**
     * Retrieves the value indicating whether implicit flow is allowed.
     *
     * @return bool True if implicit flow is allowed, false otherwise.
     */
    public function getAllowImplicitFlow(): bool
    {
        return $this->allowImplicitFlow;
    }

    /**
     * Sets the value indicating whether implicit flow is allowed.
     *
     * @param bool $allow True to allow implicit flow, false otherwise.
     */
    public function setAllowImplicitFlow(bool $allow): void
    {
        $this->allowImplicitFlow = $allow;
    }

    /**
     * Adds registration parameters to the OpenID Connect configuration.
     *
     * This method merges the provided array of registration parameters with the existing ones.
     * The registration parameters are used when registering a new client with the OpenID Connect provider.
     *
     * @param array $param The registration parameters to be added.
     */
    public function addRegistrationParam(array $param): void
    {
        $this->registrationParams = array_merge($this->registrationParams, $param);
    }

    /**
     * Retrieves the registration parameters for the OpenID Connect provider.
     *
     * @return array The registration parameters.
     */
    public function getRegistrationParams(): array
    {
        return $this->registrationParams;
    }

    /**
     * Sets the client ID for the OpenID Connect provider.
     *
     * @param string $clientID The client ID.
     */
    public function setClientID(string $clientID): void
    {
        $this->clientID = $clientID;
    }

    /**
     * Retrieves the client ID for the OpenID Connect provider.
     *
     * @return string|null The client ID or null if not set.
     */
    public function getClientID(): ?string
    {
        return $this->clientID;
    }

    /**
     * Sets the client secret for the OpenID Connect provider.
     *
     * @param string $clientSecret The client secret.
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Retrieves the client secret for the OpenID Connect provider.
     *
     * @return string|null The client secret or null if not set.
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * Sets the redirect URL for the OpenID Connect provider.
     *
     * @param string $url The redirect URL.
     * @throws OpenIDConnectClientException
     */
    public function setRedirectURL(string $url): void
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new OpenIDConnectClientException('The redirect URL is not a valid URL');
        }
        $this->redirectURL = $url;
    }

    /**
     * Retrieves the redirect URL for the OpenID Connect provider.
     *
     * @return string The redirect URL or null if not set.
     * @throws OpenIDConnectClientException
     */
    public function getRedirectURL(): string
    {
        if ($this->redirectURL === null || ! filter_var($this->redirectURL, FILTER_VALIDATE_URL)) {
            throw new OpenIDConnectClientException('The redirect URL is not a valid URL');
        }
        return $this->redirectURL;
    }

    /**
     * Sets the provider URL for the OpenID Connect provider.
     *
     * This method validates the provided URL and sets it as the provider URL.
     * If the provider URL is not already set in the internal configuration, it is added.
     *
     * @param string $providerUrl The URL of the OpenID Connect provider.
     *
     * @throws OpenIDConnectClientException If the provided URL is not a valid URL.
     */
    public function setProviderURL(string $providerUrl): void
    {
        if (! filter_var($providerUrl, FILTER_VALIDATE_URL)) {
            throw new OpenIDConnectClientException('The provider URL is not a valid URL');
        }
        $this->providerConfig['provider_url'] = $providerUrl;
    }

    /**
     * Sets the encoding type for the OpenID Connect provider.
     *
     * @param int $encType The encoding type.
     */
    public function setEncodingType(int $encType): void
    {
        if ($encType === PHP_QUERY_RFC1738 || $encType === PHP_QUERY_RFC3986) {
            $this->encType = $encType;
        }
    }

    /**
     * Retrieves the encoding type for the OpenID Connect provider.
     *
     * @return int The encoding type.
     */
    public function getEncodingType(): int
    {
        return $this->encType;
    }

    /**
     * Retrieves the leeway value for the OpenID Connect provider.
     *
     * @return int The leeway value.
     */
    public function getLeeway(): int
    {
        return $this->leeway;
    }

    /**
     * Sets the leeway value for the OpenID Connect provider.
     *
     * @param int $leeway The leeway value.
     */
    public function setLeeway(int $leeway): void
    {
        $this->leeway = $leeway;
    }

    /**
     * Sets the scope for the OpenID Connect provider.
     *
     * This method merges the provided array of scopes with the existing ones.
     * The scope determines the level of access requested by the client to the user's information.
     *
     * @param array $scope The array of scopes to be added. Each scope represents a specific type of access.
     *
     * @throws OpenIDConnectClientException If the provided scope is not an array.
     */
    public function setScope(array $scope): void
    {
        if (! is_array($scope)) {
            throw new OpenIDConnectClientException('The scope must be an array');
        }
        $this->scopes = array_merge($this->scopes, $scope);
    }

    /**
     * Retrieves the scope for the OpenID Connect provider.
     *
     * @return array The scope.
     */
    public function getScope(): array
    {
        return $this->scopes;
    }

    /**
     * Retrieves the authentication parameters for the OpenID Connect provider.
     *
     * @return array The authentication parameters.
     */
    public function getAuthParams(): array
    {
        return $this->authParams;
    }

    /**
     * Sets the authentication parameters for the OpenID Connect provider.
     *
     * @param array $params The authentication parameters.
     */
    public function setAuthParams(array $params): void
    {
        $this->authParams = $params;
    }

    /**
     * Adds an authentication parameter for the OpenID Connect provider.
     *
     * @param string $key The key of the authentication parameter.
     * @param string $value The value of the authentication parameter.
     */
    public function addAuthParam(string $key, string $value): void
    {
        $this->authParams[$key] = $value;
    }

    /**
     * Retrieves a specific provider configuration value.
     *
     * @param string $key The key of the configuration value.
     * @param string|null $default The default value to return if the key is not found.
     * @return string The configuration value or the default value if not found.
     * @throws OpenIDConnectClientException
     */
    public function getProviderConfigValue(string $key, string $default = null): string
    {
        if (! isset($this->providerConfig[$key])) {
            $this->providerConfig[$key] = $this->getWellKnownConfigValue($key, $default);
        }
        return $this->providerConfig[$key];
    }

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
     *
     * @throws OpenIDConnectClientException If the well-known configuration value for the issuer cannot be fetched.
     */
    public function getWellKnownIssuer(bool $appendSlash = false): string
    {
        $issuer = $this->getWellKnownConfigValue('issuer');
        return (is_string($issuer) ? $issuer : '') . ($appendSlash ? '/' : '');
    }

    /**
     * Retrieves the provider URL for the OpenID Connect provider.
     *
     * This method checks if the provider URL has been set in the internal configuration.
     * If the provider URL is not set, it throws an OpenIDConnectClientException with an appropriate error message.
     *
     * @return string The provider URL.
     *
     * @throws OpenIDConnectClientException If the provider URL has not been set.
     */
    public function getProviderURL(): string
    {
        if (! isset($this->providerConfig['provider_url'])) {
            throw new OpenIDConnectClientException('The provider URL has not been set');
        }

        return $this->providerConfig['provider_url'];
    }

    /**
     * Sets up the initial configuration for the OpenID Connect service.
     *
     * This method validates and sets the necessary configuration parameters such as provider URL, client ID, client secret,
     * redirect URL, and scopes. If any required parameter is missing, it throws an OpenIDConnectClientException.
     *
     * @param array $config The initial configuration settings.
     *
     * @throws OpenIDConnectClientException If any required configuration parameter is missing.
     */
    protected function setUpConfig(array $config): void
    {
        if (! isset($config['provider_url'])) {
            throw new OpenIDConnectClientException('The provider URL has not been set');
        }
        if (! isset($config['client_id'])) {
            throw new OpenIDConnectClientException('The client ID has not been set');
        }
        if (! isset($config['client_secret'])) {
            throw new OpenIDConnectClientException('The client secret has not been set');
        }
        if (! isset($config['redirect_url'])) {
            throw new OpenIDConnectClientException('The redirect URL has not been set');
        }
        if (! isset($config['scopes'])) {
            $config['scopes'] = ['openid'];
        }
        $this->setProviderConfig($config);
        $this->setProviderURL($config['provider_url']);
        $this->setClientID($config['client_id']);
        $this->setClientSecret($config['client_secret']);
        $this->setRedirectURL($config['redirect_url']);
        $this->setScope($config['scopes']);
    }

    /**
     * Sets the provider configuration for the OpenID Connect service.
     *
     * This method takes an array of configuration parameters and assigns them to the internal providerConfig property.
     * The providerConfig property holds the configuration settings for the OpenID Connect provider.
     *
     * @param array $providerConfig The array of configuration parameters.
     */
    protected function setProviderConfig(array $providerConfig): void
    {
        $this->providerConfig = $providerConfig;
    }

    /**
     * Retrieves a value from the well-known configuration.
     *
     * This method fetches the well-known configuration from the OpenID Connect provider's URL,
     * if it has not been fetched yet. It then returns the value of the specified parameter.
     * If the parameter is not found, it returns the default value or throws an exception.
     *
     * @param string $param The key of the configuration value to retrieve.
     * @param string|null $default (optional) The default value to return if the parameter is not found. Default is null.
     *
     * @return string|array The value of the specified parameter or the default value if not found.
     *
     * @throws OpenIDConnectClientException If the well-known configuration cannot be fetched or the parameter is not found.
     */
    protected function getWellKnownConfigValue(string $param, string $default = null): string|array
    {
        if ($this->wellKnownConfig === null) {
            $this->fetchWellKnownConfig();
        }

        return $this->wellKnownConfig[$param] ?? $default ?? throw new OpenIDConnectClientException("The provider $param could not be fetched. Make sure your provider has a well-known configuration available.");
    }

    /**
     * Fetches the well-known configuration from the OpenID Connect provider.
     *
     * This method constructs the well-known configuration URL using the provider URL and fetches the configuration.
     * If any parameters are set for the well-known configuration, they are appended to the URL.
     * The fetched configuration is then stored in the wellKnownConfig property.
     *
     * @throws OpenIDConnectClientException If the well-known configuration cannot be fetched or if the HTTP request fails.
     */
    private function fetchWellKnownConfig(): void
    {
        $wellKnownUrl = rtrim($this->getProviderURL(), '/') . '/.well-known/openid-configuration';

        if ($this->wellKnownConfigParameters !== []) {
            $wellKnownUrl .= '?' . http_build_query($this->wellKnownConfigParameters);
        }

        try {
            $response = Http::get($wellKnownUrl);
            if ($response->failed()) {
                throw new OpenIDConnectClientException('Failed to fetch well-known configuration');
            }

            /** @var ?array $fetchedWellKnownConfig */
            $fetchedWellKnownConfig = $response->json();

            $this->wellKnownConfig = $fetchedWellKnownConfig;
        } catch (Exception $e) {
            throw new OpenIDConnectClientException('Error fetching well-known configuration: ' . $e->getMessage());
        }
    }
}
