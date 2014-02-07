<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IMDC\TerpTubeBundle\Entity\Permissions;

/**
 * Forum
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
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $creator;
    
    /**
     * @var \DateTime
     */
    private $creationDate;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $titleMedia;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $threads;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $forumAdmins;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $forumModerators;

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
     * Returns all threads for the forum that have public access
     * 
     * @return multitype:|boolean
     */
    public function getPublicThreads()
    {
        return array_filter($this->threads->toArray(), function($t) { return $t->getPermissions()->getAccessLevel() == Permissions::ACCESS_PUBLIC; });
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
}