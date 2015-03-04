<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Entity\Thread;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

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
     * @param ObjectIdentity $objectIdentity
     */
    public function __construct(AccessType $accessType, ObjectIdentity $objectIdentity)
    {
        if (empty($accessType)) {
            throw new \InvalidArgumentException('$accessType cannot be empty.');
        }

        if (empty($objectIdentity)) {
            throw new \InvalidArgumentException('$objectIdentity cannot be empty.');
        }

        $this->accessType = $accessType;
        $this->objectIdentity = $objectIdentity;
    }

    /**
     * @param $object
     * @return AccessObjectIdentity
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public static function fromAccessObject($object)
    {
        if (!$object instanceof Forum && !$object instanceof Thread) {
            throw new \InvalidArgumentException('$object is not a forum or thread.');
        }

        $accessType = $object->getAccessType();

        if ($accessType->getId() == AccessType::TYPE_GROUP && $object instanceof Thread) {
            throw new \InvalidArgumentException('Group access type not allowed for threads.');
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($object);

        return new self($accessType, $objectIdentity);
    }

    /**
     * @return AccessType
     */
    public function getAccessType()
    {
        return $this->accessType;
    }

    /**
     * @return ObjectIdentity
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

    public function setSecurityIdentities($securityIdentities)
    {
        $this->securityIdentities = $securityIdentities;
    }
}
