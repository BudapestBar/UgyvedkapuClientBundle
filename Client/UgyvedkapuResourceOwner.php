<?php

namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Client;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class UgyvedkapuResourceOwner implements ResourceOwnerInterface
{

    /**
     * Raw response
     *
     * @var array
     */

    protected $response;
    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->response['username'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getKasz()
    {
        return $this->response['kasz'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getKapcsolatiKod()
    {
        return $this->response['kapcsolatiKod'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getProfile()
    {

        return $this->response['profil'] ?: null;
    }

    /**
     * Get user userId
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

}