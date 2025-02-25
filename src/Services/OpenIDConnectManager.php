<?php

declare(strict_types=1);

namespace CreativeCrafts\LaravelOpenidConnect\Services;

use CreativeCrafts\LaravelOpenidConnect\Contracts\OpenIdConnectManagerContract;
use CreativeCrafts\LaravelOpenidConnect\Exceptions\OpenIDConnectClientException;
use CreativeCrafts\LaravelOpenidConnect\Helpers\Base64Helper;
use Exception;
use Illuminate\Http\Client\ConnectionException;

final class OpenIDConnectManager implements OpenIdConnectManagerContract
{
    protected OpenIDConnectTokenManager $tokenManager;

    protected OpenIDConnectHttpClient $httpClient;

    protected OpenIDConnectJWTProcessor $jwtProcessor;

    protected OpenIDConnectConfig $config;

    /**
     * @throws OpenIDConnectClientException
     */
    public function __construct(array $config)
    {
        $this->setTokenManager(new OpenIDConnectTokenManager());
        $this->setHttpClient(new OpenIDConnectHttpClient());
        $this->setJwtProcessor(new OpenIDConnectJWTProcessor());
        $this->setConfig($config);
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function setConfig(array $config): void
    {
        $this->config = new OpenIDConnectConfig($config);
    }

    public function setHttpClient(OpenIDConnectHttpClient $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function setTokenManager(OpenIDConnectTokenManager $tokenManager): void
    {
        $this->tokenManager = $tokenManager;
    }

    public function setJwtProcessor(OpenIDConnectJWTProcessor $jwtProcessor): void
    {
        $this->jwtProcessor = $jwtProcessor;
    }

    /**
     * Authenticates the user using OpenID Connect.
     *
     * @return bool Returns true if the user is successfully authenticated, false otherwise.
     * @throws OpenIDConnectClientException|ConnectionException
     * @throws Exception
     */
    public function authenticate(): bool
    {
        if (isset($_REQUEST['error'])) {
            $desc = isset($_REQUEST['error_description']) ? ' Description: ' . $_REQUEST['error_description'] : '';
            throw new OpenIDConnectClientException('Error: ' . $_REQUEST['error'] . $desc);
        }

        if (isset($_REQUEST['code'])) {
            return $this->handleAuthorizationCodeFlow();
        }

        if (isset($_REQUEST['id_token']) && $this->config->getAllowImplicitFlow()) {
            return $this->handleImplicitFlow();
        }

        $this->requestAuthorization();
        return false;
    }

    /**
     * @param string|null $attribute optional
     *
     * Attribute        Type        Description
     * user_id          string      REQUIRED Identifier for the End-User at the Issuer.
     * name             string      End-User's full name in displayable form including all name parts, ordered according to End-User's locale and preferences.
     * given_name       string      Given name or first name of the End-User.
     * family_name      string      Surname or last name of the End-User.
     * middle_name      string      Middle name of the End-User.
     * nickname         string      Casual name of the End-User that may or may not be the same as the given_name. For instance, a nickname value of Mike might be returned alongside a given_name value of Michael.
     * profile          string      URL of End-User's profile page.
     * picture          string      URL of the End-User's profile picture.
     * website          string      URL of End-User's web page or blog.
     * email            string      The End-User's preferred e-mail address.
     * verified         boolean     True if the End-User's e-mail address has been verified; otherwise false.
     * gender           string      The End-User's gender: Values defined by this specification are female and male. Other values MAY be used when neither of the defined values are applicable.
     * birthday         string      The End-User's birthday, represented as a date string in MM/DD/YYYY format. The year MAY be 0000, indicating that it is omitted.
     * zoneinfo         string      String from zoneinfo [zoneinfo] time zone database. For example, Europe/Paris or America/Los_Angeles.
     * locale           string      The End-User's locale, represented as a BCP47 [RFC5646] language tag. This is typically an ISO 639-1 Alpha-2 [ISO639‑1] language code in lowercase and an ISO 3166-1 Alpha-2 [ISO3166‑1] country code in uppercase, separated by a dash. For example, en-US or fr-CA. As a compatibility note, some implementations have used an underscore as the separator rather than a dash, for example, en_US; Implementations MAY choose to accept this locale syntax as well.
     * phone_number     string      The End-User's preferred telephone number. E.164 [E.164] is RECOMMENDED as the format of this Claim. For example, +1 (425) 555-1212 or +56 (2) 687 2400.
     * address          JSON object The End-User's preferred address. The value of the address member is a JSON [RFC4627] structure containing some or all of the members defined in Section 2.4.2.1.
     * updated_time     string      Time the End-User's information was last updated, represented as an RFC 3339 [RFC3339] datetime. For example, 2011-01-03T23:58:42+0000.
     *
     * @throws OpenIDConnectClientException|ConnectionException
     */
    public function requestUserInfo(string $attribute = null, ?bool $addOpenIdSchema = false): mixed
    {
        /** @var string $userInfoEndpoint */
        $userInfoEndpoint = $this->config->getProviderConfigValue('userinfo_endpoint');
        if ($addOpenIdSchema === true) {
            $userInfoEndpoint .= '?schema=openid';
        }

        $headers = [
            'Authorization' => 'Bearer ' . $this->tokenManager->getAccessToken(),
            'Accept' => 'application/json',
        ];

        $response = $this->httpClient->fetchViaPostMethod($userInfoEndpoint, null, $headers);

        if ($this->httpClient->getResponseCode() !== 200) {
            throw new OpenIDConnectClientException(
                'The communication to retrieve user data has failed with status code ' . $this->httpClient->getResponseCode()
            );
        }

        $userJson = $this->processResponse($response);

        if ($attribute === null) {
            return $userJson;
        }

        return $userJson->$attribute ?? null;
    }

    /**
     * Handle the authorization code flow
     *
     * @throws ConnectionException
     * @throws OpenIDConnectClientException
     * @throws Exception
     */
    protected function handleAuthorizationCodeFlow(): bool
    {
        /** @var string $code */
        $code = $_REQUEST['code'];

        $tokenJson = $this->requestTokens($code);
        if (isset($tokenJson['error'])) {
            $errorDescription = $tokenJson['error_description'] ?? 'Got response: ' . $tokenJson['error'];
            throw new OpenIDConnectClientException($errorDescription);
        }

        if (! isset($_REQUEST['state']) || ($_REQUEST['state'] !== $this->tokenManager->getState())) {
            throw new OpenIDConnectClientException('Unable to determine state. State: ' . $this->tokenManager->getState());
        }

        $this->tokenManager->unsetState();

        if (! isset($tokenJson['id_token'])) {
            throw new OpenIDConnectClientException('User did not authorize openid scope.');
        }
        /** @var string $idToken */
        $idToken = $tokenJson['id_token'];
        $idTokenHeaders = $this->jwtProcessor->decodeJWT($idToken);

        if (isset($idTokenHeaders->enc)) {
            // Handle JWE
            $idToken = $this->handleJweResponse($idToken);
        }

        /** @var object $claims */
        $claims = $this->jwtProcessor->decodeJWT($idToken, 1);
        $this->jwtProcessor->verifyJWTSignature($idToken, $this->getJwks());

        $this->tokenManager->setIdToken($idToken);

        /** @var string $accessToken */
        $accessToken = $tokenJson['access_token'];
        /** @var string $refreshToken */
        $refreshToken = $tokenJson['refresh_token'] ?? null;

        $this->tokenManager->setAccessToken($accessToken);
        if ($this->verifyJWTClaims($claims, $accessToken)) {
            $this->tokenManager->unsetNonce();
            $this->tokenManager->setTokenResponse($tokenJson);
            $this->tokenManager->setRefreshToken($refreshToken);
            return true;
        }

        throw new OpenIDConnectClientException('Unable to verify JWT claims');
    }

    /**
     * Handle the implicit flow which involves directly receiving tokens from the authorization response.
     *
     * The implicit flow is a part of the OAuth 2.0 and OpenID Connect specifications, where tokens are returned directly to the client without an authorization code exchange.
     * This flow is typically used in single-page applications.
     *
     * @throws ConnectionException
     * @throws OpenIDConnectClientException
     */
    protected function handleImplicitFlow(): bool
    {
        $idToken = $_REQUEST['id_token'];
        $accessToken = $_REQUEST['access_token'] ?? null;

        if (! isset($_REQUEST['state']) || ($_REQUEST['state'] !== $this->tokenManager->getState())) {
            throw new OpenIDConnectClientException('Unable to determine state');
        }

        $this->tokenManager->unsetState();

        /** @var object $claims */
        $claims = $this->jwtProcessor->decodeJWT($idToken, 1);
        $this->jwtProcessor->verifyJWTSignature($idToken, $this->getJwks());

        $this->tokenManager->setIdToken($idToken);

        if ($this->verifyJWTClaims($claims, $accessToken)) {
            $this->tokenManager->unsetNonce();
            if ($accessToken) {
                $this->tokenManager->setAccessToken($accessToken);
            }
            return true;
        }

        throw new OpenIDConnectClientException('Unable to verify JWT claims');
    }

    /**
     * Requests tokens from the OpenID Connect provider using the authorization code.
     *
     * @param string $code The authorization code received from the provider.
     * @return array The response containing the tokens (access token, refresh token, id token) in JSON format.
     * @throws OpenIDConnectClientException|ConnectionException If there is an error during the HTTP request.
     */
    protected function requestTokens(string $code): array
    {
        /** @var string $tokenEndpoint */
        $tokenEndpoint = $this->config->getProviderConfigValue('token_endpoint');
        $tokenParams = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config->getRedirectURL(),
            'client_id' => $this->config->getClientID(),
            'client_secret' => $this->config->getClientSecret(),
        ];

        $headers = $this->prepareAuthHeaders();

        $fetchToken = $this->httpClient->fetchViaPostMethod($tokenEndpoint, http_build_query($tokenParams), $headers);

        /** @var array $response */
        $response = json_decode($fetchToken, true);
        return $response;
    }

    /**
     * Initiates the authorization process by redirecting the user to the OpenID Connect provider's authorization endpoint.
     * This function constructs the authorization request URL, including the necessary parameters such as response type,
     * redirect URI, client ID, nonce, state, and scope. It then redirects the user to the authorization endpoint.
     *
     * @throws Exception
     */
    protected function requestAuthorization(): void
    {
        /** @var string $authEndpoint */
        $authEndpoint = $this->config->getProviderConfigValue('authorization_endpoint');
        $this->tokenManager->setNonce($this->tokenManager->generateRandString());
        $nonce = $this->tokenManager->getNonce();
        if ($nonce === null || $nonce === '') {
            throw new OpenIDConnectClientException('Unable to generate nonce');
        }
        $this->tokenManager->setState($this->tokenManager->generateRandString());
        $state = $this->tokenManager->getState();

        if ($state === null || $state === '') {
            throw new OpenIDConnectClientException('Unable to generate state');
        }

        if ($this->config->getRedirectURL() === '') {
            throw new OpenIDConnectClientException('Redirect URL is not set');
        }

        if ($this->config->getClientID() === null || $this->config->getClientID() === '') {
            throw new OpenIDConnectClientException('Client ID is not set');
        }

        if ($this->config->getScope() === []) {
            throw new OpenIDConnectClientException('Scope is not set');
        }

        $authParams = array_merge($this->config->getAuthParams(), [
            'response_type' => 'code',
            'redirect_uri' => $this->config->getRedirectURL(),
            'client_id' => $this->config->getClientID(),
            'nonce' => $nonce,
            'state' => $state,
            'scope' => 'openid',
        ]);
        if ($this->config->getScope() !== []) {
            $authParams = array_merge($authParams, [
                'scope' => implode(' ', array_unique(array_merge($this->config->getScope(), ['openid']))),
            ]);
        }

        // If the client supports Proof Key for Code Exchange (PKCE)
        $codeChallengeMethod = $this->jwtProcessor->getCodeChallengeMethod();

        /** @var array $providerSuppoertedCodeChallengeMethods */
        $providerSuppoertedCodeChallengeMethods = $this->config->getProviderConfigValue('code_challenge_methods_supported');
        if (! ($codeChallengeMethod === false || ($codeChallengeMethod === '' || $codeChallengeMethod === '0')) && in_array(
            $codeChallengeMethod,
            $providerSuppoertedCodeChallengeMethods,
            true
        )) {
            $codeVerifier = $this->tokenManager->generateRandString(64);
            $this->tokenManager->setCodeVerifier($codeVerifier);
            if (! empty($this->jwtProcessor->pkceSupportedAlgs()[$codeChallengeMethod])) {
                $codeChallenge = Base64Helper::b64urlEncode(hash(
                    $this->jwtProcessor->pkceSupportedAlgs()[$codeChallengeMethod],
                    $codeVerifier,
                    true
                ));
            } else {
                $codeChallenge = $codeVerifier;
            }

            $authParams['code_challenge'] = array_merge($authParams, [
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallenge,
            ]);
        }
        // $authEndpoint .= (!str_contains($authEndpoint, '?') ? '?' : '&') . http_build_query($authParams, '', '&', $this->config->getEncodingType());
        $authEndpoint .= (strpos($authEndpoint, '?') === false ? '?' : '&') . http_build_query($authParams, '', '&', $this->config->getEncodingType());
        // $authEndpoint .= '?' . http_build_query($authParams);
        $this->tokenManager->commitSession();
        redirect()->to($authEndpoint)->send();
    }

    /**
     * Prepares the headers for the authorization request.
     *
     * This function constructs the headers required for the authorization request to the OpenID Connect provider.
     * It includes the 'Authorization' header with a Basic authentication scheme using the client ID and client secret,
     * and the 'Accept' header set to 'application/json'.
     *
     * @return array The headers for the authorization request.
     */
    protected function prepareAuthHeaders(): array
    {
        /** @var string $clientId */
        $clientId = $this->config->getClientID();

        /** @var string $clientSecret */
        $clientSecret = $this->config->getClientSecret();
        return [
            'Authorization' => 'Basic ' . base64_encode(urlencode($clientId) . ':' . urlencode($clientSecret)),
            'Accept' => 'application/json',
        ];
    }

    /**
     * Retrieves the JSON Web Key Set (JWKS) from the OpenID Connect provider.
     *
     * The JWKS is a set of public keys used to verify the signatures of JSON Web Tokens (JWTs).
     * This function sends a request to the JWKS URI specified in the OpenID Connect configuration,
     * decodes the response, and returns the JWKS as an array.
     *
     * @return array The JSON Web Key Set (JWKS) as an array.
     * @throws OpenIDConnectClientException|ConnectionException If there is an error during the HTTP request.
     */
    protected function getJwks(): array
    {
        /** @var string $jwksUri */
        $jwksUri = $this->config->getProviderConfigValue('jwks_uri');
        $response = $this->httpClient->fetchViaPostMethod($jwksUri);
        /** @var array $fetchedJwks */
        $fetchedJwks = json_decode($response, true);
        return $fetchedJwks['keys'];
    }

    /**
     * Verifies the JWT claims received from the OpenID Connect provider.
     *
     * @param object $claims The decoded JWT claims as an object.
     * @param string $accessToken The access token received from the provider.
     * @return bool Returns true if the JWT claims are valid, false otherwise.
     * @throws OpenIDConnectClientException
     */
    protected function verifyJWTClaims(object $claims, string $accessToken): bool
    {
        $claimsArray = get_object_vars($claims);

        // Validate Issuer
        /** @var string $issuer */
        $issuer = $claimsArray['iss'];
        $expectedIssuer = $this->config->getWellKnownIssuer();
        if ($issuer !== $expectedIssuer && ! $this->validateIssuer($issuer)) {
            return false;
        }

        // Validate Audience
        $audience = is_array($claimsArray['aud']) ? $claimsArray['aud'] : [$claimsArray['aud']];
        if (! in_array($this->config->getClientID(), $audience, true)) {
            return false;
        }

        // Validate Subject
        if (! isset($claims->sub)) {
            return false;
        }

        // Validate Expiration
        if (isset($claims->exp) && (is_int($claims->exp) && $claims->exp < time() - $this->config->getLeeway())) {
            return false;
        }

        // Validate Not Before
        if (isset($claims->nbf) && (is_int($claims->nbf) && $claims->nbf > time() + $this->config->getLeeway())) {
            return false;
        }

        // Validate Nonce
        if (isset($claims->nonce) && $claims->nonce !== $this->tokenManager->getNonce()) {
            return false;
        }
        // Validate Access Token Hash
        return ! (isset($claims->at_hash) && ! $this->validateAccessTokenHash($claims->at_hash, $accessToken));
    }

    /**
     * Validates the issuer of the JWT.
     * This function compares the provided issuer with the issuer value retrieved from the OpenID Connect configuration.
     * If they match, it returns true; otherwise, it returns false.
     *
     * @param string $issuer The issuer of the JWT to be validated.
     * @return bool Returns true if the issuer is valid, false otherwise.
     * @throws OpenIDConnectClientException
     */
    protected function validateIssuer(string $issuer): bool
    {
        return $issuer === $this->config->getProviderConfigValue('issuer');
    }

    /**
     * Validates the access token hash from the JWT claims.
     *
     * This function extracts the 'alg' value from the ID token, calculates the expected 'at_hash' value using the
     * SHA-256 hash of the access token, and compares it with the provided 'at_hash' value.
     *
     * @param string $atHash The 'at_hash' value from the JWT claims.
     * @param string $accessToken The access token received from the OpenID Connect provider.
     * @return bool Returns true if the 'at_hash' value is valid, false otherwise.
     */
    protected function validateAccessTokenHash(string $atHash, string $accessToken): bool
    {
        /** @var string $tokenId */
        $tokenId = $this->tokenManager->getIdToken();
        $alg = $this->jwtProcessor->decodeJWT($tokenId)->alg ?? 'RS256';
        $bit = substr($alg, 2, 3);
        $len = ((int) $bit) / 16;
        $expectedAtHash = $this->jwtProcessor->urlEncode(substr(hash('sha' . $bit, $accessToken, true), 0, $len));

        return $atHash === $expectedAtHash;
    }

    /**
     * Handles a JSON Web Encryption (JWE) response.
     *
     * This function decrypts a JWE using a provided JSON Web Key (JWK) and returns the decrypted payload.
     *
     * @param string $jwe The JSON Web Encryption (JWE) to be decrypted.
     * @return string The decrypted payload.
     * @throws OpenIDConnectClientException If the decrypted payload is not a string.
     */
    protected function handleJweResponse(string $jwe): string
    {
        // Create a JWK (JSON Web Key) for decryption
        /*$key = new JWK([
        }
        return $decryptedPayload;*/
        throw new OpenIDConnectClientException('JWE response is not supported at the moment.');
    }

    /**
     * @throws OpenIDConnectClientException
     * @throws ConnectionException
     */
    private function processResponse(string $response): object
    {
        if ($this->httpClient->getResponseContentType() === 'application/jwt') {
            $jwtHeaders = $this->jwtProcessor->decodeJWT($response);

            $jwt = isset($jwtHeaders->enc) ? $this->handleJweResponse($response) : $response;

            $this->jwtProcessor->verifyJWTSignature($jwt, $this->getJwks());

            /** @var object $claims */
            $claims = $this->jwtProcessor->decodeJWT($jwt, 1);
            /** @var string $accessToken */
            $accessToken = $this->tokenManager->getAccessToken();
            if (! $this->verifyJWTClaims($claims, $accessToken)) {
                throw new OpenIDConnectClientException('Invalid JWT signature');
            }

            return $claims;
        }

        /** @var object $responseObject */
        $responseObject = json_decode($response, false);

        return $responseObject;
    }
}
