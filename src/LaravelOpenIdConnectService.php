<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect;

use CreativeCrafts\LaravelOpenidConnect\Contracts\LaravelOpenIdConnectServiceContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class LaravelOpenIdConnectService implements LaravelOpenIdConnectServiceContract
{
    /**
     * Sends a POST request to the specified URL with the provided data.
     *
     * @throws ConnectionException If a connection error occurs.
     */
    public static function post(string $url, array $data): Response
    {
        $response = Http::asForm()->post($url, $data);
        if ($response->failed()) {
            throw new ConnectionException("HTTP request failed with status: " . $response->status());
        }

        return $response;
    }

    /**
     * Sends a GET request to the specified URL with the provided access token.
     *
     * This function uses Laravel's HTTP client to send a GET request to the specified URL.
     * The access token is included in the request headers for authentication.
     *
     * @throws ConnectionException If a connection error occurs.
     */
    public static function get(string $url, string $accessToken): Response
    {
        $response =  Http::withToken($accessToken)->get($url);
        if ($response->failed()) {
            throw new ConnectionException("HTTP request failed with status: " . $response->status());
        }

        return $response;
    }
}
