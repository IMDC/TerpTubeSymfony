<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use IMDC\TerpTubeBundle\Entity\UserGroup;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * Class GroupSecurityIdentity
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class GroupSecurityIdentity implements SecurityIdentityInterface
{
    private $id;
    private $class;

    public function __construct($id, $class)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('$id must not be empty.');
        }

        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        $this->id = intval($id);
        $this->class = $class;
    }

    public static function fromGroup(UserGroup $group)
    {
        return new self($group->getId(), ClassUtils::getRealClass($group));
    }

    public static function fromRoleSecurityIdentity(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof RoleSecurityIdentity) {
            throw new \InvalidArgumentException('');
        }

        $securityIdentifier = $identity->getRole();

        $id = substr($securityIdentifier, 1 + $pos = strpos($securityIdentifier, '-'));
        if (!is_numeric($id))
            $id = -1;

        return new self(
            $id,
            substr($securityIdentifier, 0, $pos));
    }

    public function toRoleSecurityIdentity()
    {
        return new RoleSecurityIdentity($this->getClass().'-'.$this->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof GroupSecurityIdentity) {
            return false;
        }

        return $this->class === $identity->getClass() && $this->id === $identity->getId();
    }

    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}