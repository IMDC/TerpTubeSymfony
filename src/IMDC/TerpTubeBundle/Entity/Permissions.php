<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permissions
 */
class Permissions
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $publicAccess;

    /**
     * @var boolean
     */
    private $usersWithLinkHaveAccess;

    /**
     * @var boolean
     */
    private $privateAccess;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $friendsOfMember;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupsWithAccess;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersWithAccess;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupsWithAccess = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersWithAccess  = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set publicAccess
     *
     * @param boolean $publicAccess
     * @return Permissions
     */
    public function setPublicAccess($publicAccess)
    {
        $this->publicAccess = $publicAccess;
    
        return $this;
    }

    /**
     * Get publicAccess
     *
     * @return boolean 
     */
    public function getPublicAccess()
    {
        return $this->publicAccess;
    }

    /**
     * Set usersWithLinkHaveAccess
     *
     * @param boolean $usersWithLinkHaveAccess
     * @return Permissions
     */
    public function setUsersWithLinkHaveAccess($usersWithLinkHaveAccess)
    {
        $this->usersWithLinkHaveAccess = $usersWithLinkHaveAccess;
    
        return $this;
    }

    /**
     * Get usersWithLinkHaveAccess
     *
     * @return boolean 
     */
    public function getUsersWithLinkHaveAccess()
    {
        return $this->usersWithLinkHaveAccess;
    }

    /**
     * Set privateAccess
     *
     * @param boolean $privateAccess
     * @return Permissions
     */
    public function setPrivateAccess($privateAccess)
    {
        $this->privateAccess = $privateAccess;
    
        return $this;
    }

    /**
     * Get privateAccess
     *
     * @return boolean 
     */
    public function getPrivateAccess()
    {
        return $this->privateAccess;
    }

    
    /**
     * Set friendsOfMember
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $friendsOfMember
     * @return Permissions
     */
    public function setFriendsOfMember(\IMDC\TerpTubeBundle\Entity\User $friendsOfMember = null)
    {
        $this->friendsOfMember = $friendsOfMember;
    
        return $this;
    }

    /**
     * Get friendsOfMember
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getFriendsOfMember()
    {
        return $this->friendsOfMember;
    }

    /**
     * Add groupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess
     * @return Permissions
     */
    public function addGroupsWithAccess(\IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess)
    {
        $this->groupsWithAccess[] = $groupsWithAccess;
    
        return $this;
    }

    /**
     * Remove groupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess
     */
    public function removeGroupsWithAccess(\IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess)
    {
        $this->groupsWithAccess->removeElement($groupsWithAccess);
    }

    /**
     * Get groupsWithAccess
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupsWithAccess()
    {
        return $this->groupsWithAccess;
    }

    /**
     * Add groupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess
     * @return Permissions
     */
    public function addGroupsWithAcces(\IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess)
    {
        $this->groupsWithAccess[] = $groupsWithAccess;
    
        return $this;
    }

    /**
     * Remove groupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess
     */
    public function removeGroupsWithAcces(\IMDC\TerpTubeBundle\Entity\UserGroup $groupsWithAccess)
    {
        $this->groupsWithAccess->removeElement($groupsWithAccess);
    }

    /**
     * Add usersWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersWithAccess
     * @return Permissions
     */
    public function addUsersWithAcces(\IMDC\TerpTubeBundle\Entity\User $usersWithAccess)
    {
        $this->usersWithAccess[] = $usersWithAccess;
    
        return $this;
    }

    /**
     * Remove usersWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersWithAccess
     */
    public function removeUsersWithAcces(\IMDC\TerpTubeBundle\Entity\User $usersWithAccess)
    {
        $this->usersWithAccess->removeElement($usersWithAccess);
    }

    /**
     * Get usersWithAccess
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersWithAccess()
    {
        return $this->usersWithAccess;
    }
}