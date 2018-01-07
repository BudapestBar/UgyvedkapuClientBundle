<?php

namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Userbundle\Entity\User;

use BudapestBar\Bundle\UgyvedkapuClientBundle\Security\Core\Authentication\Token\UgyvedkapuToken;

use BudapestBar\Bundle\UgyvedkapuClientBundle\Client\UgyvedkapuOAuthProvider;
use BudapestBar\Bundle\UgyvedkapuClientBundle\Client\UgyvedkapuResourceOwner;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SecurityController extends Controller
{


    /**
     * @Route("/ugyvedkapu")
     * @Template()
     */
    public function connectAction(Request $request)
    {

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
    
            return $this->redirectToRoute('AppBundle:Default:index');    

        }

        $provider = $this->get('BudapestBar\Bundle\UgyvedkapuClientBundle\Client\UgyvedkapuOAuthProvider');

        $code   = $request->query->get('code', null);
        $state  = $request->query->get('state', null);

        // If we don't have an authorization code then get one
        if (!$code && !$request->query->get('error')) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            $this->get('session')->set('oauth2state', $provider->getState());

            // Redirect the user to the authorization URL.
            return new RedirectResponse($authorizationUrl);

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (!$state || ($state !==  $this->get('session')->get('oauth2state'))) {

            $this->get('session')->remove('oauth2state');
            
            throw new \Exception('invalid state'); 

        }

        
        return [];

    }

}
