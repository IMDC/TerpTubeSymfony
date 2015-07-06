<?php

namespace IMDC\TerpTubeBundle\Component\Serializer\Exclusion;

use IMDC\TerpTubeBundle\Entity\User;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Symfony\Component\Security\Core\SecurityContext;

class UserExclusionStrategy implements ExclusionStrategyInterface
{
    protected static $loggedInOnlyProperties = array(
        'mentorList',
        'menteeList',
        'createdInvitations'
    );

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var boolean
     */
    protected $isLoggedInUser;

    public function __construct(SecurityContext $securityContext = null)
    {
        $this->securityContext = $securityContext;
        if ($this->securityContext)
            $this->user = $this->securityContext->getToken()->getUser();
        $this->isLoggedInUser = false;
    }

    public function checkUser(User $user)
    {
        $this->isLoggedInUser = $this->user == $user;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        if ($this->isLoggedInUser)
            return $metadata->name != 'IMDC\TerpTubeBundle\Entity\User';

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        if (in_array($property->name, self::$loggedInOnlyProperties))
            return !$this->isLoggedInUser;

        return false;
    }
}
