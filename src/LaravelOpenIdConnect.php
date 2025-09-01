<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use CreativeCrafts\LaravelOpenIdConnect\Contracts\LaravelOpenIdConnectContract;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectConfig;
use CreativeCrafts\LaravelOpenidConnect\Services\OpenIDConnectManager;
use Illuminate\Http\Client\ConnectionException;
use Psr\SimpleCache\InvalidArgumentException;

final class LaravelOpenIdConnect implements LaravelOpenIdConnectContract
{
    protected string $provider;

    protected OpenIDConnectManager $connectManager;

    protected OpenIDConnectConfig $config;

    /**
     * @throws OpenIDConnectClientException
     */
    public function acceptProvider(?string $provider = null): self
    {
        /** @var array $appConfig */
        $appConfig = config('openid-connect');
        $this->provider = $provider ?? $appConfig['default_provider'];
        $configData = $appConfig['providers'][$provider];
        if (! isset($configData['redirect_url'])) {
            $configData['redirect_url'] = $appConfig['default_redirect_url'];
        }
        $this->connectManager = new OpenIDConnectManager(
            $configData
        );
        return $this;
    }

    /**
     * @throws OpenIDConnectClientException
     * @throws ConnectionException
     * @throws InvalidArgumentException
     */
    public function authenticate(): bool
    {
        return $this->connectManager->authenticate();
    }

    /**
     * @throws OpenIDConnectClientException
     * @throws ConnectionException
     */
    public function retrieveUserInfo(?string $attribute = null, ?bool $addOpenIdSchema = false): mixed
    {
        return $this->connectManager->requestUserInfo($attribute, $addOpenIdSchema);
    }
}
