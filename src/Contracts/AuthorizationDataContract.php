<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface AuthorizationDataContract
{
    public function __construct(
        array $providerAuthorisationConfig,
        string $issuerUrl
    );

    public function queryParameters(): string;

    public function authorizationUrl(): string;
}
