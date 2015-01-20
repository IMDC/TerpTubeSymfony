<?php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use IMDC\TerpTubeBundle\Utils\Utils;

/**
 * UserGroup
 */
class UserGroup extends BaseGroup
{
    protected $id;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $visibleToPublic;

    /**
     * @var boolean
     */
    private $visibleToRegisteredUsers;

    /**
     * @var boolean
     */
    private $openForNewMembers;

    /**
     * @var boolean
     */
    private $joinByInvitationOnly;

    /**
     * @var boolean
     */
    private $membersCanAddForums;

    /**
     * @var array
     */
    private $mediaDisplayOrder;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $forums;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $userFounder;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $moderators;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $admins;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $media;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(null);

        $this->forums       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->members      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moderators   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->admins       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->media        = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return UserGroup
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    
        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return UserGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set visibleToPublic
     *
     * @param boolean $visibleToPublic
     * @return UserGroup
     */
    public function setVisibleToPublic($visibleToPublic)
    {
        $this->visibleToPublic = $visibleToPublic;

        return $this;
    }

    /**
     * Get visibleToPublic
     *
     * @return boolean
     */
    public function getVisibleToPublic()
    {
        return $this->visibleToPublic;
    }

    /**
     * Set visibleToRegisteredUsers
     *
     * @param boolean $visibleToRegisteredUsers
     * @return UserGroup
     */
    public function setVisibleToRegisteredUsers($visibleToRegisteredUsers)
    {
        $this->visibleToRegisteredUsers = $visibleToRegisteredUsers;

        return $this;
    }

    /**
     * Get visibleToRegisteredUsers
     *
     * @return boolean
     */
    public function getVisibleToRegisteredUsers()
    {
        return $this->visibleToRegisteredUsers;
    }

    /**
     * Set openForNewMembers
     *
     * @param boolean $openForNewMembers
     * @return UserGroup
     */
    public function setOpenForNewMembers($openForNewMembers)
    {
        $this->openForNewMembers = $openForNewMembers;

        return $this;
    }

    /**
     * Get openForNewMembers
     *
     * @return boolean
     */
    public function getOpenForNewMembers()
    {
        return $this->openForNewMembers;
    }

    /**
     * Set joinByInvitationOnly
     *
     * @param boolean $joinByInvitationOnly
     * @return UserGroup
     */
    public function setJoinByInvitationOnly($joinByInvitationOnly)
    {
        $this->joinByInvitationOnly = $joinByInvitationOnly;

        return $this;
    }

    /**
     * Get joinByInvitationOnly
     *
     * @return boolean
     */
    public function getJoinByInvitationOnly()
    {
        return $this->joinByInvitationOnly;
    }

    /**
     * Set membersCanAddForums
     *
     * @param boolean $membersCanAddForums
     * @return UserGroup
     */
    public function setMembersCanAddForums($membersCanAddForums)
    {
        $this->membersCanAddForums = $membersCanAddForums;

        return $this;
    }

    /**
     * Get membersCanAddForums
     *
     * @return boolean
     */
    public function getMembersCanAddForums()
    {
        return $this->membersCanAddForums;
    }

    /**
     * Set mediaDisplayOrder
     *
     * @param array $mediaDisplayOrder
     * @return UserGroup
     */
    public function setMediaDisplayOrder($mediaDisplayOrder)
    {
        $this->mediaDisplayOrder = $mediaDisplayOrder;

        return $this;
    }

    /**
     * Get mediaDisplayOrder
     *
     * @return array
     */
    public function getMediaDisplayOrder()
    {
        return $this->mediaDisplayOrder;
    }

    /**
     * Add forums
     *
     * @param \IMDC\TerpTubeBundle\Entity\Forum $forums
     * @return UserGroup
     */
    public function addForum(\IMDC\TerpTubeBundle\Entity\Forum $forums)
    {
        $this->forums[] = $forums;

        return $this;
    }

    /**
     * Remove forums
     *
     * @param \IMDC\TerpTubeBundle\Entity\Forum $forums
     */
    public function removeForum(\IMDC\TerpTubeBundle\Entity\Forum $forums)
    {
        $this->forums->removeElement($forums);
    }

    /**
     * Get forums
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getForums()
    {
        return $this->forums;
    }

    /**
     * Set userFounder
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $userFounder
     * @return UserGroup
     */
    public function setUserFounder(\IMDC\TerpTubeBundle\Entity\User $userFounder = null)
    {
        $this->userFounder = $userFounder;
    
        return $this;
    }

    /**
     * Get userFounder
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getUserFounder()
    {
        return $this->userFounder;
    }

    /**
     * Add members
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $members
     * @return UserGroup
     */
    public function addMember(\IMDC\TerpTubeBundle\Entity\User $members)
    {
        $this->members[] = $members;
    
        return $this;
    }

    /**
     * Remove members
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $members
     */
    public function removeMember(\IMDC\TerpTubeBundle\Entity\User $members)
    {
        $this->members->removeElement($members);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add moderators
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $moderators
     * @return UserGroup
     */
    public function addModerator(\IMDC\TerpTubeBundle\Entity\User $moderators)
    {
        $this->moderators[] = $moderators;
    
        return $this;
    }

    /**
     * Remove moderators
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $moderators
     */
    public function removeModerator(\IMDC\TerpTubeBundle\Entity\User $moderators)
    {
        $this->moderators->removeElement($moderators);
    }

    /**
     * Get moderators
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModerators()
    {
        return $this->moderators;
    }

    /**
     * Add admins
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $admins
     * @return UserGroup
     */
    public function addAdmin(\IMDC\TerpTubeBundle\Entity\User $admins)
    {
        $this->admins[] = $admins;
        
        return $this;
    }

    /**
     * Remove admins
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $admins
     */
    public function removeAdmin(\IMDC\TerpTubeBundle\Entity\User $admins)
    {
        $this->admins->removeElement($admins);
    }

    /**
     * Get admins
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * Add media
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $media
     * @return UserGroup
     */
    public function addMedia(\IMDC\TerpTubeBundle\Entity\Media $media)
    {
        $this->media[] = $media;

        return $this;
    }

    /**
     * Remove media
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $media
     */
    public function removeMedia(\IMDC\TerpTubeBundle\Entity\Media $media)
    {
        $this->media->removeElement($media);
    }

    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedia()
    {
        return $this->media;
    }

    public function setDateCreatedToNow()
    {
        $this->dateCreated = new \DateTime('NOW');
        return $this;
    }

    /**
     * @param \IMDC\TerpTubeBundle\Entity\User $user
     * @return boolean
     */
    public function isUserMemberOfGroup(\IMDC\TerpTubeBundle\Entity\User $user)
    {
        return $this->members->contains($user);
    }

    /**
     * @param \IMDC\TerpTubeBundle\Entity\User $user
     * @return boolean
     */
    public function isUserModerator(\IMDC\TerpTubeBundle\Entity\User $user)
    {
        return $this->moderators->contains($user);
    }

    /**
     * @param \IMDC\TerpTubeBundle\Entity\User $user
     * @return boolean
     */
    public function isUserAdmin(\IMDC\TerpTubeBundle\Entity\User $user)
    {
        return $this->admins->contains($user);
    }

    public function getOrderedMedia()
    {
        return Utils::orderMedia($this->getMedia(), $this->getMediaDisplayOrder());
    }
    
    public function __toString()
    {
        return (string)$this->getName();
    }
}
