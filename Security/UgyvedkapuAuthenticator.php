<?php

// src/AppBundle/Security/TokenAuthenticator.php
namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use BudapestBar\Bundle\UgyvedkapuClientBundle\Security\Authentication\Token\UgyvedkapuToken;


class UgyvedkapuAuthenticator implements SimplePreAuthenticatorInterface
{

    protected $httpUtils;
    protected $session;

    public function __construct(HttpUtils $httpUtils, SessionInterface $session)
    {

        $this->httpUtils    = $httpUtils;
        $this->session      = $session;
    
    }

    public function createToken(Request $request, $providerKey)
    {


        $code   = $request->query->get('code', null);
        $state  = $request->query->get('state', null);


        if (!$this->httpUtils->checkRequestPath($request, 'ugyvedkapu_client_connect') || $code == null || $state == null) {
        
            return;
        
        }

        if ($state !==  $this->session->get('oauth2state')) {

            $this->session->remove('oauth2state');
            
            throw new \Exception('invalid state'); 

        }

        return new UgyvedkapuToken(
            'anon.',
            $code,
            $providerKey
        );

    }



    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UgyvedkapuToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof UgyvedkapuProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of UgyvedkapuProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $authCode                   = $token->getCredentials();
        list($userId, $accessToken) = $userProvider->getIDForAuthCode($authCode);

        // User is the Entity which represents your user
        $user = $token->getUser();

        if ($user instanceof User) {

            $authenticatedToken = new UgyvedkapuToken(
                $user,
                $authCode,
                $providerKey,
                $user->getRoles()
            );

            $authenticatedToken->setAccessToken($token->getAccessToken());

            return $authenticatedToken;

        }

        if (!$userId) {
            // this message will be returned to the client
            throw new CustomUserMessageAuthenticationException(
                sprintf('API Key "%s" does not exist.', $authCode)
            );
        }

        $user = $userProvider->loadUserByUsername($userId);

        $user->addRole('ROLE_OAUTH');

        $authenticatedToken = new UgyvedkapuToken(
            $user,
            $authCode,
            $providerKey,
            $user->getRoles()
        );

        $authenticatedToken->setAccessToken($accessToken);

        return $authenticatedToken;
    }
}