<?php

namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Client;

use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class UgyvedkapuOAuthProvider extends AbstractProvider
{

    private $env;

    use BearerAuthorizationTrait;

    /**
     * @var string Key used in the access token response to identify the resource owner.
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = [];

    /**
     * Api version
     *
     * @var string
     */
    public $version = 'v1';

    /**
     * @param mixed $env
     *
     * @return self
     */
    public function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return sprintf('https://ugyvedkapu.hu/%soauth/authorize', ($this->env == 'dev') ? 'app_dev.php/' : '');
    }
    
    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return sprintf('https://ugyvedkapu.hu/%soauth/token', ($this->env == 'dev') ? 'app_dev.php/' : ''); 
    }
    
    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return sprintf('https://ugyvedkapu.hu/%soauth/resource', ($this->env == 'dev') ? 'app_dev.php/' : '');
    }
    
    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @link https://developer.uber.com/v1/api-reference/
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {

        $acceptableStatuses = [200, 201];

        if (!in_array($response->getStatusCode(), $acceptableStatuses)) {
            throw new IdentityProviderException(

                $data["message"] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param object $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {

        return new UgyvedkapuResourceOwner($response);
    }


}