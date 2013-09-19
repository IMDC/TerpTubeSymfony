<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Thread
 */
class Thread
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var boolean
     */
    private $locked;

    /**
     * @var boolean
     */
    private $sticky;
    
    /**
     * @var string
     */
    private $content;
    
    /**
     * @var integer
     */
    private $lastPostID;
    
    /**
     * @var \DateTime
     */
    private $lastPostAt;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var integer
     */
    private $type;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $creator;

    /**
     * @var array
     */
    private $tags;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersFollowing;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $posts;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usersFollowing = new \Doctrine\Common\Collections\ArrayCollection();
        $this->posts          = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Thread
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
     * Set locked
     *
     * @param boolean $locked
     * @return Thread
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    
        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set sticky
     *
     * @param boolean $sticky
     * @return Thread
     */
    public function setSticky($sticky)
    {
        $this->sticky = $sticky;
    
        return $this;
    }

    /**
     * Get sticky
     *
     * @return boolean 
     */
    public function getSticky()
    {
        return $this->sticky;
    }


    /**
     * Set content
     *
     * @param string $content
     * @return Thread
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set lastPostID
     *
     * @param integer $lastPostID
     * @return Thread
     */
    public function setLastPostID($lastPostID)
    {
        $this->lastPostID = $lastPostID;
    
        return $this;
    }

    /**
     * Get lastPostID
     *
     * @return integer 
     */
    public function getLastPostID()
    {
        return $this->lastPostID;
    }

    /**
     * Set lastPostAt
     *
     * @param \DateTime $lastPostAt
     * @return Thread
     */
    public function setLastPostAt($lastPostAt)
    {
        $this->lastPostAt = $lastPostAt;
    
        return $this;
    }

    /**
     * Get lastPostAt
     *
     * @return \DateTime 
     */
    public function getLastPostAt()
    {
        return $this->lastPostAt;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Thread
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Thread
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creator
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $creator
     * @return Thread
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
     * Set tags
     *
     * @param array $tags
     * @return Thread
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    
        return $this;
    }

    /**
     * Get tags
     *
     * @return array 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add usersFollowing
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersFollowing
     * @return Thread
     */
    public function addUsersFollowing(\IMDC\TerpTubeBundle\Entity\User $usersFollowing)
    {
        $this->usersFollowing[] = $usersFollowing;
    
        return $this;
    }

    /**
     * Remove usersFollowing
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersFollowing
     */
    public function removeUsersFollowing(\IMDC\TerpTubeBundle\Entity\User $usersFollowing)
    {
        $this->usersFollowing->removeElement($usersFollowing);
    }

    /**
     * Get usersFollowing
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersFollowing()
    {
        return $this->usersFollowing;
    }

    /**
     * Add posts
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $posts
     * @return Thread
     */
    public function addPost(\IMDC\TerpTubeBundle\Entity\Post $posts)
    {
        $this->posts[] = $posts;
    
        return $this;
    }

    /**
     * Remove posts
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $posts
     */
    public function removePost(\IMDC\TerpTubeBundle\Entity\Post $posts)
    {
        $this->posts->removeElement($posts);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPosts()
    {
        return $this->posts;
    }
}