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
    private $name;
    private $class;

    public function __construct($name, $class)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('$name must not be empty.');
        }

        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        $this->name = $name;
        $this->class = $class;
    }

    public static function fromGroup(UserGroup $group)
    {
        return new self($group->getName(), ClassUtils::getRealClass($group));
    }

    public static function fromSecurityIdentity(SecurityIdentityInterface $identity)
    {
        if ($identity instanceof RoleSecurityIdentity) {
            $securityIdentifier = $identity->getRole();
            return new self(
                substr($securityIdentifier, 1 + $pos = strpos($securityIdentifier, '-')),
                substr($securityIdentifier, 0, $pos));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof GroupSecurityIdentity) {
            return false;
        }

        return $this->class === $identity->getClass() && $this->name === $identity->getName();
    }

    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}