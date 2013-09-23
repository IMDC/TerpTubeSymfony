<?php
// src/IMDC\TerpTubeBundle/Entity/User.php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{
    protected $id;
	protected $profile;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	protected $sentMessages;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	protected $receivedMessages;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $readMessages;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $archivedMessages;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $deletedMessages;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $userGroups;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $resourceFiles;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $friendsList;
	
	/**
	 * @var \DateTime
	 */
	private $joinDate;
	
	/**
	 * @var integer
	 */
	private $postCount = 0;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	//private $createdThreads;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $posts;
	
	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $threads;
	
    public function __construct()
    {
        parent::__construct();
        $this->sentMessages     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->receivedMessages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->readMessages     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->archivedMessages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deletedMessages  = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userGroups       = new \Doctrine\Common\Collections\ArrayCollection();
        $this->resourceFiles    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->friendsList      = new \Doctrine\Common\Collections\ArrayCollection();
        //$this->createdThreads   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->posts            = new \Doctrine\Common\Collections\ArrayCollection();
        $this->threads          = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add resourceFiles
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $resourceFiles
     * @return User
     */
    public function addResourceFile(\IMDC\TerpTubeBundle\Entity\Media $resourceFiles)
    {
        $this->resourceFiles[] = $resourceFiles;
    
        return $this;
    }

    /**
     * Remove resourceFiles
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $resourceFiles
     */
    public function removeResourceFile(\IMDC\TerpTubeBundle\Entity\Media $resourceFiles)
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
    /**
     * Get the number of unread private messages for the user
     * 
     * @return number
     */
    public function getNumUnreadPMs() 
    {
        $msg_count = 0;
        $allrecmessages = $this->getReceivedMessages();
        foreach ($allrecmessages as $mess) {
            if ( !$mess->isMessageRead($this) ) {
                $msg_count++;
            }
        }
        return $msg_count;
    }

    /**
     * Add archivedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $archivedMessages
     * @return User
     */
    public function addArchivedMessage(\IMDC\TerpTubeBundle\Entity\Message $archivedMessages)
    {
        $this->archivedMessages[] = $archivedMessages;
    
        return $this;
    }

    /**
     * Remove archivedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $archivedMessages
     */
    public function removeArchivedMessage(\IMDC\TerpTubeBundle\Entity\Message $archivedMessages)
    {
        $this->archivedMessages->removeElement($archivedMessages);
    }

    /**
     * Get archivedMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchivedMessages()
    {
        return $this->archivedMessages;
    }

    /**
     * Add deletedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $deletedMessages
     * @return User
     */
    public function addDeletedMessage(\IMDC\TerpTubeBundle\Entity\Message $deletedMessages)
    {
        $this->deletedMessages[] = $deletedMessages;
    
        return $this;
    }

    /**
     * Remove deletedMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $deletedMessages
     */
    public function removeDeletedMessage(\IMDC\TerpTubeBundle\Entity\Message $deletedMessages)
    {
        $this->deletedMessages->removeElement($deletedMessages);
    }

    /**
     * Get deletedMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDeletedMessages()
    {
        return $this->deletedMessages;
    }

    /**
     * Add readMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $readMessages
     * @return User
     */
    public function addReadMessage(\IMDC\TerpTubeBundle\Entity\Message $readMessages)
    {
        $this->readMessages[] = $readMessages;
    
        return $this;
    }

    /**
     * Remove readMessages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $readMessages
     */
    public function removeReadMessage(\IMDC\TerpTubeBundle\Entity\Message $readMessages)
    {
        $this->readMessages->removeElement($readMessages);
    }

    /**
     * Get readMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReadMessages()
    {
        return $this->readMessages;
    }

    /**
     * Add userGroups
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $userGroups
     * @return User
     */
    public function addUserGroup(\IMDC\TerpTubeBundle\Entity\UserGroup $userGroups)
    {
        $this->userGroups[] = $userGroups;
    
        return $this;
    }

    /**
     * Remove userGroups
     *
     * @param \IMDC\TerpTubeBundle\Entity\UserGroup $userGroups
     */
    public function removeUserGroup(\IMDC\TerpTubeBundle\Entity\UserGroup $userGroups)
    {
        $this->userGroups->removeElement($userGroups);
    }

    /**
     * Get userGroups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * Add friendsList
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $friendsList
     * @return User
     */
    public function addFriendsList(\IMDC\TerpTubeBundle\Entity\User $friendsList)
    {
        $this->friendsList[] = $friendsList;
    
        return $this;
    }

    /**
     * Remove friendsList
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $friendsList
     */
    public function removeFriendsList(\IMDC\TerpTubeBundle\Entity\User $friendsList)
    {
        $this->friendsList->removeElement($friendsList);
    }

    /**
     * Get friendsList
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFriendsList()
    {
        return $this->friendsList;
    }
    
    public function isUserOnFriendsList(\IMDC\TerpTubeBundle\Entity\User $user)
    {
        return $this->friendsList->contains($user);
    }

    /**
     * Set joinDate
     *
     * @param \DateTime $joinDate
     * @return User
     */
    public function setJoinDate($joinDate)
    {
        $this->joinDate = $joinDate;
    
        return $this;
    }

    public function setJoinDateToNow()
    {
        $this->joinDate = new \DateTime('NOW');
        
        return $this;
    }
    
    /**
     * Get joinDate
     *
     * @return \DateTime 
     */
    public function getJoinDate()
    {
        return $this->joinDate;
    }

    /**
     * Set postCount
     *
     * @param integer $postCount
     * @return User
     */
    public function setPostCount($postCount)
    {
        $this->postCount = $postCount;
    
        return $this;
    }

    /**
     * Get postCount
     *
     * @return integer 
     */
    public function getPostCount()
    {
        //return $this->postCount;
        return count($this->getPosts());
    }


    /**
     * Add createdThreads
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $createdThreads
     * @return User
     */
    public function addCreatedThread(\IMDC\TerpTubeBundle\Entity\Thread $createdThreads)
    {
        $this->createdThreads[] = $createdThreads;
    
        return $this;
    }

    /**
     * Remove createdThreads
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $createdThreads
     */
    public function removeCreatedThread(\IMDC\TerpTubeBundle\Entity\Thread $createdThreads)
    {
        $this->createdThreads->removeElement($createdThreads);
    }

    /**
     * Get createdThreads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCreatedThreads()
    {
        return $this->createdThreads;
    }

    /**
     * Add posts
     *
     * @param \IMDC\TerpTubeBundle\Entity\Post $posts
     * @return User
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

    /**
     * Add threads
     *
     * @param \IMDC\TerpTubeBundle\Entity\Thread $threads
     * @return User
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
}