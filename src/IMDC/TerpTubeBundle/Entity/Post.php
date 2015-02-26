<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IMDC\TerpTubeBundle\Utils\Utils;

/**
 * Post
 */
class Post
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $content;
    
    /**
     * I don't believe this field is used anywhere and probably could be removed (paul)
     * @var boolean
     */
    private $isDeleted;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $editedAt;

    /**
     * @var float
     */
    private $startTime;
    
    /**
     * @var float
     */
    private $endTime;
    
    /**
     * @var boolean
     */
    private $isTemporal;

    /**
     * @var array
     */
    private $mediaDisplayOrder;

    /**
     * This is a collection in order to support inclusion of multiple files per post
     * such as a transcript file, a captions file, etc.
     * (at a later date) (paul)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $attachedFile;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $replies;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Post
     */
    private $parentPost;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $author;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\Thread
     */
    private $parentThread;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $editedBy;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attachedFile = new \Doctrine\Common\Collections\ArrayCollection();
        $this->replies      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isDeleted    = FALSE; // set to false as default
        $this->isTemporal   = FALSE; // set to false as default
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
     * Set content
     *
     * @param string $content
     * @return Post
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return Post
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Post
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @return Post
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;
    
        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime 
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set startTime
     *
     * @param float $startTime
     * @return Post
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param float $endTime
     * @return Post
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return float
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set isTemporal
     *
     * @param boolean $isTemporal
     * @return Post
     */
    public function setIsTemporal($isTemporal)
    {
        $this->isTemporal = $isTemporal;

        return $this;
    }

    /**
     * Get isTemporal
     *
     * @return boolean
     */
    public function getIsTemporal()
    {
        return $this->isTemporal;
    }

    /**
     * Set mediaDisplayOrder
     *
     * @param array $mediaDisplayOrder
     * @return Post
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
     * Add attachedFile
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $attachedFile
     * @return Post
     */
    public function addAttachedFile(\IMDC\TerpTubeBundle\Entity\Media $attachedFile)
    {
        $this->attachedFile[] = $attachedFile;

        return $this;
    }

    /**
     * Remove attachedFile
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $attachedFile
     */
    public function removeAttachedFile(\IMDC\TerpTubeBundle\Entity\Media $attachedFile)
    {
        $this->attachedFile->removeElement($attachedFile);
    }

    public function setAttachedFile($attachedFile)
    {
        $this->attachedFile = $attachedFile;

        return $this;
    }

    /**
     * Get attachedFile
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachedFile()
    {
        return $this->attachedFile;
    }

    /**
     * Add replies
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $replies
     * @return Post
     */
    public function addReplies(\IMDC\TerpTubeBundle\Entity\Post $replies)
    {
        $this->replies[] = $replies;

        return $this;
    }

    /**
     * Remove replies
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $replies
     */
    public function removeReplie(\IMDC\TerpTubeBundle\Entity\Post $replies)
    {
        $this->replies->removeElement($replies);
    }

    /**
     * Get replies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set parentPost
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $parentPost
     * @return Post
     */
    public function setParentPost(\IMDC\TerpTubeBundle\Entity\Post $parentPost = null)
    {
        $this->parentPost = $parentPost;

        return $this;
    }

    /**
     * Get parentPost
     *
     * @return \IMDC\TerpTubeBundle\Entity\Post
     */
    public function getParentPost()
    {
        return $this->parentPost;
    }

    /**
     * Set author
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $author
     * @return Post
     */
    public function setAuthor(\IMDC\TerpTubeBundle\Entity\User $author = null)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set parentThread
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $parentThread
     * @return Post
     */
    public function setParentThread(\IMDC\TerpTubeBundle\Entity\Thread $parentThread = null)
    {
        $this->parentThread = $parentThread;
    
        return $this;
    }

    /**
     * Get parentThread
     *
     * @return \IMDC\TerpTubeBundle\Entity\Thread 
     */
    public function getParentThread()
    {
        return $this->parentThread;
    }

    /**
     * Set editedBy
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $editedBy
     * @return Post
     */
    public function setEditedBy(\IMDC\TerpTubeBundle\Entity\User $editedBy = null)
    {
        $this->editedBy = $editedBy;
    
        return $this;
    }

    /**
     * Get editedBy
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }

    /**
     * Add replies
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $replies
     * @return Post
     */
    public function addReply(\IMDC\TerpTubeBundle\Entity\Post $replies)
    {
        $this->replies[] = $replies;

        return $this;
    }

    /**
     * Remove replies
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $replies
     */
    public function removeReply(\IMDC\TerpTubeBundle\Entity\Post $replies)
    {
        $this->replies->removeElement($replies);
    }

    public function getOrderedMedia()
    {
        return Utils::orderMedia($this->getAttachedFile(), $this->getMediaDisplayOrder());
    }
}
