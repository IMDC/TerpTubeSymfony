<?php
// src/IMDC\TerpTubeBundle/Entity/User.php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{
    protected $id;
	protected $profile;
	
	protected $sentMessages;
	protected $receivedMessages;

    public function __construct()
    {
        parent::__construct();
        $this->sentMessages     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->receivedMessages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set profile
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserProfile $profile
     * @return User
     */
    public function setProfile(\IMDC\TerpTubeBundle\Entity\UserProfile $profile = null)
    {
        $this->profile = $profile;
    
        return $this;
    }

    /**
     * Get profile
     *
     * @return \IMDC\TerpTubeBundle\Entity\UserProfile 
     */
    public function getProfile()
    {
        return $this->profile;
    }


    /**
     * Add sentMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $sentMessages
     * @return User
     */
    public function addSentMessage(\IMDC\TerpTubeBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages[] = $sentMessages;
    
        return $this;
    }

    /**
     * Remove sentMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $sentMessages
     */
    public function removeSentMessage(\IMDC\TerpTubeBundle\Entity\Message $sentMessages)
    {
        $this->sentMessages->removeElement($sentMessages);
    }

    /**
     * Get sentMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * Add receivedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $receivedMessages
     * @return User
     */
    public function addReceivedMessage(\IMDC\TerpTubeBundle\Entity\Message $receivedMessages)
    {
        $this->receivedMessages[] = $receivedMessages;
    
        return $this;
    }

    /**
     * Remove receivedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $receivedMessages
     */
    public function removeReceivedMessage(\IMDC\TerpTubeBundle\Entity\Message $receivedMessages)
    {
        $this->receivedMessages->removeElement($receivedMessages);
    }

    /**
     * Get receivedMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReceivedMessages()
    {
        return $this->receivedMessages;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resourceFiles;


    /**
     * Add resourceFiles
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resourceFiles
     * @return User
     */
    public function addResourceFile(\IMDC\TerpTubeBundle\Entity\ResourceFile $resourceFiles)
    {
        $this->resourceFiles[] = $resourceFiles;
    
        return $this;
    }

    /**
     * Remove resourceFiles
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resourceFiles
     */
    public function removeResourceFile(\IMDC\TerpTubeBundle\Entity\ResourceFile $resourceFiles)
    {
        $this->resourceFiles->removeElement($resourceFiles);
    }

    /**
     * Get resourceFiles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResourceFiles()
    {
        return $this->resourceFiles;
    }
}