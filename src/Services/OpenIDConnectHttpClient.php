<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Services;

use CreativeCrafts\LaravelOpenidConnect\Contracts\OpenIDConnectHttpClientContract;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class OpenIDConnectHttpClient implements OpenIDConnectHttpClientContract
{
    // @pest-mutate-ignore
    private int $timeOut = 60;

    // @pest-mutate-ignore
    private bool $verifyPeer = true;

    // @pest-mutate-ignore
    private ?string $httpProxy = null;

    // @pest-mutate-ignore
    private ?string $certPath = null;

    private int $responseCode;

    // @pest-mutate-ignore
    private ?string $responseContentType = null;

    /**
     * Sets the HTTP proxy to be used for making requests.
     *
     * @param string $httpProxy The HTTP proxy URL.
     */
    public function setHttpProxy(string $httpProxy): void
    {
        $this->httpProxy = $httpProxy;
    }

    /**
     * Sets the path to the SSL certificate for verifying the peer.
     *
     * @param string $certPath The path to the SSL certificate.
     */
    public function setCertPath(string $certPath): void
    {
        $this->certPath = $certPath;
    }

    /**
     * Sets the timeout for the HTTP request in seconds.
     *
     * @param int $timeout The timeout value in seconds.
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeOut = $timeout;
    }

    /**
     * Sets whether to verify the peer's SSL certificate.
     *
     * @param bool $verifyPeer True to verify the peer's SSL certificate, false otherwise.
     */
    public function setVerifyPeer(bool $verifyPeer): void
    {
        $this->verifyPeer = $verifyPeer;
    }

    /**
     * Fetches the content of a URL using the specified HTTP method and headers.
     *
     * @param string $url The URL to fetch.
     * @param string|null $postBody The body of the POST request. If null, a GET request is made.
     * @param array $headers Additional headers to send with the request.
     * @return string The response content as a string.
     * @throws OpenIDConnectClientException|ConnectionException
     */
    public function fetchViaPostMethod(string $url, ?string $postBody = null, array $headers = []): string
    {
        $response = Http::withHeaders($headers)
            ->withOptions([
                'verify' => $this->verifyPeer,
                'timeout' => $this->timeOut,
                'proxy' => $this->httpProxy,
                'cert' => $this->certPath,
            ])
            ->withBody($postBody ?? '', $this->determineContentType($postBody))
            ->post($url);

        $this->responseCode = $response->status();
        $this->responseContentType = $response->header('Content-Type');

        if ($response->failed()) {
            throw new OpenIDConnectClientException('HTTP error: ' . $response->status());
        }

        return $response->body();
    }

    /**
     * Fetches the content of a URL using the GET method.
     *
     * This method sends a GET request to the specified URL with optional headers
     * and returns the response body as a string. It also updates the response code
     * and content type properties of the class.
     *
     * @param string $url     The URL to fetch.
     * @param array  $headers Additional headers to send with the request (optional).
     *
     * @return string The response body as a string.
     *
     * @throws OpenIDConnectClientException|ConnectionException If the HTTP request fails.
     */
    public function fetchViaGetMethod(string $url, array $headers = []): string
    {
        $response = Http::withHeaders($headers)
            ->withOptions([
                'verify' => $this->verifyPeer,
                'timeout' => $this->timeOut,
                'proxy' => $this->httpProxy,
                'cert' => $this->certPath,
            ])
            ->get($url);

        $this->responseCode = $response->status();
        $this->responseContentType = $response->header('Content-Type');

        if ($response->failed()) {
            throw new OpenIDConnectClientException('HTTP error: ' . $response->status());
        }

        return $response->body();
    }

    /**
     * Gets the response code of the last HTTP request.
     *
     * @return int The response code.
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * Gets the content type of the response of the last HTTP request.
     *
     * @return string|null The content type, or null if not available.
     */
    public function getResponseContentType(): ?string
    {
        return $this->responseContentType;
    }

    /**
     * Determine the content type for the HTTP request
     */
    private function determineContentType(?string $postBody): string
    {
        if ($postBody !== null && is_object(json_decode($postBody, false))) {
            return 'application/json';
        }

        return 'application/x-www-form-urlencoded';
    }
}
