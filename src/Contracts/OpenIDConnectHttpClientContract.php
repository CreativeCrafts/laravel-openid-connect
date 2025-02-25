<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Contracts;

/**
 * Interface for OpenID Connect HTTP client contract.
 *
 * This interface defines the methods required for making HTTP requests and handling responses.
 */
interface OpenIDConnectHttpClientContract
{
    /**
     * Sets the HTTP proxy to be used for making requests.
     *
     * @param string $httpProxy The HTTP proxy URL.
     */
    public function setHttpProxy(string $httpProxy): void;

    /**
     * Sets the path to the SSL certificate for verifying the peer.
     *
     * @param string $certPath The path to the SSL certificate.
     */
    public function setCertPath(string $certPath): void;

    /**
     * Sets the timeout for the HTTP request in seconds.
     *
     * @param int $timeout The timeout value in seconds.
     */
    public function setTimeout(int $timeout): void;

    /**
     * Sets whether to verify the peer's SSL certificate.
     *
     * @param bool $verifyPeer True to verify the peer's SSL certificate, false otherwise.
     */
    public function setVerifyPeer(bool $verifyPeer): void;

    /**
     * Fetches the content of a URL using the specified HTTP method and headers.
     *
     * @param string $url The URL to fetch.
     * @param string|null $postBody The body of the POST request. If null, a GET request is made.
     * @param array $headers Additional headers to send with the request.
     *
     * @return string The response content as a string.
     */
    public function fetchViaPostMethod(string $url, ?string $postBody = null, array $headers = []): string;

    /**
     * Gets the response code of the last HTTP request.
     *
     * @return int The response code.
     */
    public function getResponseCode(): int;

    /**
     * Gets the content type of the response of the last HTTP request.
     *
     * @return string|null The content type, or null if not available.
     */
    public function getResponseContentType(): ?string;
}
