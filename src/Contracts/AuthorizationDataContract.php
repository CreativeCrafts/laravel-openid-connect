<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface AuthorizationDataContract
{
    public function __construct(
        string $clientId,
        ?string $clientSecret = null,
        string $url = '',
        ?string $redirectUri = null,
        ?string $code = null,
        ?string $refreshToken = null,
        ?string $responseType = null,
        ?string $scope = null,
        ?string $state = null
    );

    public function accessTokenData(): array;

    public function refreshTokenData(): array;

    public function issuerUrl(): string;

    public function queryParameters(): string;
}
