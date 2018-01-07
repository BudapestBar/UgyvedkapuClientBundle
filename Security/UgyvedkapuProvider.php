<?php

// src/AppBundle/Security/User/WebserviceUserProvider.php
namespace BudapestBar\Bundle\UgyvedkapuClientBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\ORM\EntityManagerInterface;

use BudapestBar\Bundle\UgyvedkapuClientBundle\Client\UgyvedkapuOAuthProvider;
use UserBundle\Entity\User;

class UgyvedkapuProvider implements UserProviderInterface
{

    private $entityManager;
    private $authProvider;
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UgyvedkapuOAuthProvider $authProvider, UserPasswordEncoderInterface $encoder)
    {

        $this->entityManager = $entityManager;
        $this->authProvider  = $authProvider;
        $this->encoder       = $encoder;
    
    }

    public function getIDForAuthCode($authCode) {

        if (!$authCode) {

            return [null, null];

        }

        try {


                $accessToken    = $this->authProvider->getAccessToken('authorization_code', ['code' => $authCode]);
                $resourceOwner  = $this->authProvider->getResourceOwner($accessToken);



        } catch (IdentityProviderException $e) {

            // Failed to get the access token or user details.
            exit($e->getMessage());

        }

        $user = $this->loadOrCreateUserByResourceOwner($resourceOwner);

        return [$user->getId(), $accessToken];


    }

    public function loadOrCreateUserByResourceOwner($resourceOwner) {


        $user = $this->entityManager->getRepository("UserBundle:User")->find($resourceOwner->getId());

        if (!$user) {

            $user = new User($resourceOwner->getId());
            $user->setId($resourceOwner->getId());
            $user->setUsername($resourceOwner->getId());
            $user->setPassword($this->encoder->encodePassword($user, $resourceOwner->getEmail()));

        }

        $user->setEmail($resourceOwner->getEmail());
        $user->setName($resourceOwner->getUsername());
        $user->setProfile($resourceOwner->getProfile());
        $user->setKapcsolatiKod($resourceOwner->getKapcsolatiKod());

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return $user;

    }


    public function loadUserByUsername($userId)
    {

        $user = $this->entityManager->getRepository("UserBundle:User")->find($userId);

        if ($user) {

            return $user;

        }

        throw new UsernameNotFoundException(
            sprintf('User id "%s" does not exist.', $userId)
        );
    }

    public function refreshUser(UserInterface $user)
    {

        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getId()); 

    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}