<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permissions
 */
class Permissions
{
    const ACCESS_PUBLIC = -1;
    const ACCESS_CREATOR = 0;
    const ACCESS_PRIVATE = 0; // synonym
    const ACCESS_CREATORS_FRIENDS = 1;
    const ACCESS_WITH_LINK = 2;
    const ACCESS_USER_LIST = 3;
    const ACCESS_GROUP_LIST = 4;
    const ACCESS_REGISTERED_MEMBERS = 5;
    
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $accessLevel;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userGroupsWithAccess;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersWithAccess;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userGroupsWithAccess = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersWithAccess  = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __tostring()
    {
        return (String) $this->getAccessLevel();
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
     * Set accessLevel
     *
     * @param integer $accessLevel
     * @return Permissions
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;
    
        return $this;
    }
    
    /**
     * Get accessLevel
     *
     * @return integer
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }
    
    
    public function getAccessLevelStringFromInteger()
    {
//         const ACCESS_PUBLIC = -1;
//         const ACCESS_CREATOR = 0;
//         const ACCESS_CREATORS_FRIENDS = 1;
//         const ACCESS_WITH_LINK = 2;
//         const ACCESS_USER_LIST = 3;
//         const ACCESS_GROUP_LIST = 4;
//         const ACCESS_REGISTERED_MEMBERS = 5;

        switch ($this->getAccessLevel()) {
        	case Permissions::ACCESS_PUBLIC:
        	    return 'Anyone can view';
        	    break;
        	case Permissions::ACCESS_CREATOR:
    	    case Permissions::ACCESS_CREATORS_FRIENDS:
	        case Permissions::ACCESS_USER_LIST:
	        case Permissions::ACCESS_GROUP_LIST:
    	        return 'Private';
    	        break;
	        case Permissions::ACCESS_WITH_LINK:
	            return 'Anyone with link can view';
                break;
	        case Permissions::ACCESS_REGISTERED_MEMBERS:
	            return 'Registered members only';
        	default:
        	    return 'Access not defined';
        	break;
        }
    }
    
    /**
     * Add userGroupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $userGroupsWithAccess
     * @return Permissions
     */
    public function addUserGroupsWithAccess(\IMDC\TerpTubeBundle\Entity\UserGroup $userGroupsWithAccess)
    {
        $this->userGroupsWithAccess[] = $userGroupsWithAccess;
    
        return $this;
    }

    /**
     * Remove userGroupsWithAccess
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $userGroupsWithAccess
     */
    public function removeUserGroupsWithAccess(\IMDC\TerpTubeBundle\Entity\UserGroup $userGroupsWithAccess)
    {
        $this->userGroupsWithAccess->removeElement($userGroupsWithAccess);
    }

    /**
     * Get userGroupsWithAccess
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getuserGroupsWithAccess()
    {
        return $this->userGroupsWithAccess;
    }
    
    public function setUserGroupsWithAccess($userGroupsWithAccess)
    {
        $this->userGroupsWithAccess = $userGroupsWithAccess;
        
        return $this;
    }

    
    
    public function addUsersWithAccess(\IMDC\TerpTubeBundle\Entity\User $usersWithAccess)
    {
        $this->usersWithAccess[] = $usersWithAccess;
        
        return $this;
    }
    
    
    public function removeUsersWithAccess(\IMDC\TerpTubeBundle\Entity\User $usersWithAccess)
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
    
    public function setUsersWithAccess($usersWithAccess)
    {
        $this->usersWithAccess = $usersWithAccess;
        
        return $this;
    }
}