<?php

namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
* 
*/
class UgyvedkapuToken extends PreAuthenticatedToken
{

	private $accessToken;

	/**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->accessToken, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->accessToken, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     *
     * @return self
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
    
}