<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Forum class, serves as general organization for discussion topics (Threads).
 * Each Forum has a text title and a media title, a collection of admins and mods,
 * and a list of threads it contains
 */
class Forum
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $titleText;

    /**
     * @var \DateTime
     */
    private $lastActivity;
    
    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $threads;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $titleMedia;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $forumAdmins;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $forumModerators;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\UserGroup
     */
    private $group;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\AccessType
     */
    private $accessType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->threads          = new \Doctrine\Common\Collections\ArrayCollection();
        $this->titleMedia       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->forumAdmins      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->forumModerators  = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __toString() 
    {
        return $this->getTitleText();
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
     * Set titleText
     *
     * @param string $titleText
     * @return Forum
     */
    public function setTitleText($titleText)
    {
        $this->titleText = $titleText;
    
        return $this;
    }

    /**
     * Get titleText
     *
     * @return string 
     */
    public function getTitleText()
    {
        return $this->titleText;
    }

    /**
     * Set lastActivity
     *
     * @param \DateTime $lastActivity
     * @return Forum
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    
        return $this;
    }

    /**
     * Get lastActivity
     *
     * @return \DateTime 
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Forum
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creator
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $creator
     * @return Forum
     */
    public function setCreator(\IMDC\TerpTubeBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \IMDC\TerpTubeBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add threads
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $threads
     * @return Forum
     */
    public function addThread(\IMDC\TerpTubeBundle\Entity\Thread $threads)
    {
        $this->threads[] = $threads;

        return $this;
    }

    /**
     * Remove threads
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $threads
     */
    public function removeThread(\IMDC\TerpTubeBundle\Entity\Thread $threads)
    {
        $this->threads->removeElement($threads);
    }

    /**
     * Get threads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * Add titleMedia
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $titleMedia
     * @return Forum
     */
    public function addTitleMedia(\IMDC\TerpTubeBundle\Entity\Media $titleMedia)
    {
        $this->titleMedia[] = $titleMedia;

        return $this;
    }

    /**
     * Remove titleMedia
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $titleMedia
     */
    public function removeTitleMedia(\IMDC\TerpTubeBundle\Entity\Media $titleMedia)
    {
        $this->titleMedia->removeElement($titleMedia);
    }

    /**
     * Get titleMedia
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTitleMedia()
    {
        return $this->titleMedia;
    }

    /**
     * Add forumAdmins
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $forumAdmins
     * @return Forum
     */
    public function addForumAdmin(\IMDC\TerpTubeBundle\Entity\User $forumAdmins)
    {
        $this->forumAdmins[] = $forumAdmins;
    
        return $this;
    }

    /**
     * Remove forumAdmins
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $forumAdmins
     */
    public function removeForumAdmin(\IMDC\TerpTubeBundle\Entity\User $forumAdmins)
    {
        $this->forumAdmins->removeElement($forumAdmins);
    }

    /**
     * Get forumAdmins
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForumAdmins()
    {
        return $this->forumAdmins;
    }

    /**
     * Add forumModerators
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $forumModerators
     * @return Forum
     */
    public function addForumModerator(\IMDC\TerpTubeBundle\Entity\User $forumModerators)
    {
        $this->forumModerators[] = $forumModerators;
    
        return $this;
    }

    /**
     * Remove forumModerators
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $forumModerators
     */
    public function removeForumModerator(\IMDC\TerpTubeBundle\Entity\User $forumModerators)
    {
        $this->forumModerators->removeElement($forumModerators);
    }

    /**
     * Get forumModerators
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForumModerators()
    {
        return $this->forumModerators;
    }

    /**
     * Set group
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $group
     * @return Forum
     */
    public function setGroup(\IMDC\TerpTubeBundle\Entity\UserGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \IMDC\TerpTubeBundle\Entity\UserGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set accessType
     *
     * @param \IMDC\TerpTubeBundle\Entity\AccessType $accessType
     * @return Forum
     */
    public function setAccessType(\IMDC\TerpTubeBundle\Entity\AccessType $accessType = null)
    {
        $this->accessType = $accessType;

        return $this;
    }

    /**
     * Get accessType
     *
     * @return \IMDC\TerpTubeBundle\Entity\AccessType
     */
    public function getAccessType()
    {
        return $this->accessType;
    }

    //TODO delete
    /*public function setTitleMedia(\IMDC\TerpTubeBundle\Entity\Media $titleMedia)
    {
        $this->titleMedia = new \Doctrine\Common\Collections\ArrayCollection();
        $this->titleMedia[] = $titleMedia;

        return $this;
    }*/

    public function setTitleMedia($titleMedia)
    {
        $this->titleMedia = $titleMedia;

        return $this;
    }
}
