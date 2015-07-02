<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Voter;

use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class AccessVoter
 * @package IMDC\TerpTubeBundle\Security\Acl\Voter
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessVoter implements VoterInterface
{
    const VIEW = 'view';

    private $accessProvider;
    private $container;

    /**
     * @param AccessProvider $accessProvider
     * @param ContainerInterface $container
     */
    public function __construct(AccessProvider $accessProvider, ContainerInterface $container)
    {
        $this->accessProvider = $accessProvider;
        $this->container = $container;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return in_array(strtolower($attribute), array(
            self::VIEW
        ));
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return in_array($class, array(
            'IMDC\TerpTubeBundle\Entity\Forum',
            'IMDC\TerpTubeBundle\Entity\Thread'
        ));
    }

    /**
     * @param TokenInterface $token
     * @param null|object $object
     * @param array $attributes
     * @return int
     * @throws \InvalidArgumentException
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if(count($attributes) !== 1) {
            throw new \InvalidArgumentException('Only one attribute is allowed for VIEW');
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $objectIdentity = AccessObjectIdentity::fromAccessObject($object);
        $access = $this->accessProvider->createAccess($objectIdentity);
        $this->accessProvider->setSecurityIdentities($objectIdentity, $object);
        if ($access->isGranted($token->getUser(), $this->container->get('request'), $this->container->get('router')))
            return VoterInterface::ACCESS_GRANTED;

        return VoterInterface::ACCESS_DENIED;
    }
}
