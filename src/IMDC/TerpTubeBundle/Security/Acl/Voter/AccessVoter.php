<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Voter;

use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessVoter
 * @package IMDC\TerpTubeBundle\Security\Acl\Voter
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessVoter implements VoterInterface
{
    const VIEW = 'view';

    private $accessProvider;

    /**
     * @param AccessProvider $accessProvider
     */
    public function __construct(AccessProvider $accessProvider)
    {
        $this->accessProvider = $accessProvider;
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
        $access = $this->accessProvider->getAccess($objectIdentity);
        if ($access->isGranted($token->getUser()))
            return VoterInterface::ACCESS_GRANTED;

        return VoterInterface::ACCESS_DENIED;
    }
}
