<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

use Illuminate\Http\Client\Response;

interface LaravelOpenIdConnectServiceContract
{
    public static function post(string $url, array $data): Response;

    public static function get(string $url, string $accessToken): Response;
}
