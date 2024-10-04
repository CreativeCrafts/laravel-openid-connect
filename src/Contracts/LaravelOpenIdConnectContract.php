<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

interface LaravelOpenIdConnectContract
{
    public function __construct(string $provider);

    public function getAuthorizationUrl(): string;

    public function getAccessToken(string $authorizationCode): array;

    public function getUserInfo(string $accessToken): array;

    public function refreshToken(string $refreshToken): array;
}
