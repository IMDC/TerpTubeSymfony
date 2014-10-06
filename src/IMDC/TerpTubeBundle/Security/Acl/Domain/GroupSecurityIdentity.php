<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use IMDC\TerpTubeBundle\Entity\UserGroup;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Class GroupSecurityIdentity
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class GroupSecurityIdentity implements SecurityIdentityInterface
{
    private $name;

    public function __construct($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('$name must not be empty.');
        }

        $this->name = $name;
    }

    public static function fromGroup(UserGroup $group)
    {
        return new self($group->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof GroupSecurityIdentity) {
            return false;
        }

        return $this->name === $identity->getName();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}