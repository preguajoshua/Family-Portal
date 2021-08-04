<?php

namespace App\Services;

use RuntimeException;
use Jumbojett\OpenIDConnectClient;

class AxxessIdentity
{
    /**
     * OpenID Client Instance.
     *
     * @var  Jumbojett\OpenIDConnectClient
     */
    protected $oidc;

    /**
     * Axxess Identity URL.
     *
     * @var  string
     */
    protected $url;

    /**
     * Clinet ID.
     *
     * @var  string
     */
    protected $clientId;

    /**
     * Client Secret.
     *
     * @var  string
     */
    protected $clientSecret;

    /**
     * Instantiate the Axxess Identity service.
     *
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->url = $config['url'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
    }

    /**
     * Get a bearer token.
     *
     * @return  string
     *
     * @throws \RuntimeException
     */
    public function bearerToken()
    {
        $token = $this->rawToken();

        if (! $token) {
            throw new RuntimeException('Can not obtain identity access token.');
        }

        return "Bearer {$token}";
    }

    /**
     * Get a raw access token.
     *
     * @return  string
     */
    public function rawToken()
    {
        if (is_null($this->oidc)) {
            $this->connect();
        }

        $token = $this->oidc->getAccessToken();

        if (is_null($token)) {
            return $this->requestToken();
        }

        return $token;
    }

    /**
     * Connect to the OpenID Client Instance.
     *
     * @return  void
     */
    protected function connect()
    {
        $this->oidc = new OpenIDConnectClient($this->url, $this->clientId, $this->clientSecret);
        $this->oidc->providerConfigParam(['token_endpoint' => "{$this->url}/connect/token"]);
        $this->oidc->addScope('trusted');
    }

    /**
     * Request a client credentials token.
     *
     * @return  string
     */
    protected function requestToken()
    {
        $clientCredentialsToken = $this->oidc->requestClientCredentialsToken();

        if (isset($clientCredentialsToken->access_token) && $this->oidc->verifyJWTsignature($clientCredentialsToken->access_token)) {
            $this->oidc->setAccessToken($clientCredentialsToken->access_token);

            return $clientCredentialsToken->access_token;
        }

        return '';
    }
}
