<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Voter;

use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessObjectIdentity;
use IMDC\TerpTubeBundle\Security\Acl\Domain\AccessProvider;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AccessVoter
 * @package IMDC\TerpTubeBundle\Security\Acl\Voter
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessVoter implements VoterInterface
{
    const VIEW = 'view';

    private $accessProvider;

    public function __construct(AccessProvider $accessProvider)
    {
        $this->accessProvider = $accessProvider;
    }

    public function supportsAttribute($attribute)
    {
        return in_array(strtolower($attribute), array(
            self::VIEW
        ));
    }

    public function supportsClass($class)
    {
        return in_array($class, array(
            'IMDC\TerpTubeBundle\Entity\Forum',
            'IMDC\TerpTubeBundle\Entity\Thread'
        ));
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $class = get_class($object);

        if (!$this->supportsClass($class)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if(count($attributes) !== 1) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW'
            );
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
