<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Thread;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * Class AccessObjectIdentity
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
final class AccessObjectIdentity
{
    private $accessType;
    private $objectIdentity;
    private $securityIdentities;

    /**
     * @param AccessType $accessType
     * @param $objectIdentity
     * @param array $securityIdentities
     * @throws \InvalidArgumentException
     */
    public function __construct(AccessType $accessType, $objectIdentity, array $securityIdentities = array())
    {
        if (empty($accessType)) {
            throw new \InvalidArgumentException('$accessType cannot be empty.');
        }

        if (empty($objectIdentity)) {
            throw new \InvalidArgumentException('$objectIdentity cannot be empty.');
        }

        $this->accessType = $accessType;
        $this->objectIdentity = $objectIdentity;
        $this->securityIdentities = $securityIdentities;
    }

    /**
     * @param $object
     * @param array $users
     * @return AccessObjectIdentity
     * @throws \InvalidArgumentException
     */
    public static function fromAccessObject($object, array $users = array())
    {
        if ($object instanceof Forum || $object instanceof Thread) {
            $accessType = $object->getAccessType();

            if ($accessType->getId() == AccessType::TYPE_FRIENDS) {
                $users = $object->getCreator()->getFriendsList()->toArray();
            }
            if ($object instanceof Forum && $accessType->getId() != AccessType::TYPE_GROUP) {
                $object->setGroup(null);
            }
            if ($object instanceof Thread && $accessType->getId() == AccessType::TYPE_GROUP) {
                throw new \InvalidArgumentException('Group access type not allowed for threads.');
            }

            $objectIdentity = ObjectIdentity::fromDomainObject($object);
            $securityIdentities = array();

            if ($accessType->getId() == AccessType::TYPE_USERS ||
                $accessType->getId() == AccessType::TYPE_FRIENDS) {
                foreach ($users as $user) {
                    $securityIdentities[] = UserSecurityIdentity::fromAccount($user);
                }
            }
            if ($object instanceof Forum && $accessType->getId() == AccessType::TYPE_GROUP) {
                $securityIdentities[] = GroupSecurityIdentity::fromGroup($object->getGroup());
            }

            return new self($accessType, $objectIdentity, $securityIdentities);
        } else {
            throw new \InvalidArgumentException('$object is not a forum or thread.');
        }
    }

    /**
     * @return AccessType
     */
    public function getAccessType()
    {
        return $this->accessType;
    }

    /**
     * @return mixed
     */
    public function getObjectIdentity()
    {
        return $this->objectIdentity;
    }

    /**
     * @return array
     */
    public function getSecurityIdentities()
    {
        return $this->securityIdentities;
    }
}
