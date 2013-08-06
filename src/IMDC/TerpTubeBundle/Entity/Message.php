<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 */
class Message
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \DateTime
     */
    private $sentDate;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $owner;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $recipients;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersRead;

    /**
     * @var boolean
     */
    private $tempRead;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersArchived;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usersDeleted;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recipients    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersRead     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersArchived = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usersDeleted  = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set subject
     *
     * @param string $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Message
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
     * Set sentDate
     *
     * @param \DateTime $sentDate
     * @return Message
     */
    public function setSentDate($sentDate)
    {
        $this->sentDate = $sentDate;
    
        return $this;
    }

    /**
     * Get sentDate
     *
     * @return \DateTime 
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * Set owner
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $owner
     * @return Message
     */
    public function setOwner(\IMDC\TerpTubeBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add recipients
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $recipients
     * @return Message
     */
    public function addRecipient(\IMDC\TerpTubeBundle\Entity\User $recipients)
    {
        $this->recipients[] = $recipients;
    
        return $this;
    }

    /**
     * Remove recipients
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $recipients
     */
    public function removeRecipient(\IMDC\TerpTubeBundle\Entity\User $recipients)
    {
        $this->recipients->removeElement($recipients);
    }

    /**
     * Get recipients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function setSentValue()
    {
        $this->sentDate = new \DateTime('NOW');
        return $this;
    }

    /**
     * Add usersRead
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersRead
     * @return Message
     */
    public function addUsersRead(\IMDC\TerpTubeBundle\Entity\User $usersRead)
    {
        $this->usersRead[] = $usersRead;
    
        return $this;
    }

    /**
     * Remove usersRead
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersRead
     */
    public function removeUsersRead(\IMDC\TerpTubeBundle\Entity\User $usersRead)
    {
        $this->usersRead->removeElement($usersRead);
    }

    /**
     * Get usersRead
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersRead()
    {
        return $this->usersRead;
    }
    
    /**
     * Function to check if the given user has read this message
     * 
     * @param User $user
     * @return boolean
     */
    public function isMessageRead(User $user)
    {
        if ($this->usersRead->contains($user)) {
            return TRUE;
        }
        return FALSE;
    }
    

    /**
     * Set tempRead
     *
     * @param boolean $tempRead
     * @return Message
     */
    public function setTempRead($tempRead)
    {
        $this->tempRead = $tempRead;
    
        return $this;
    }

    /**
     * Get tempRead
     *
     * @return boolean 
     */
    public function getTempRead()
    {
        return $this->tempRead;
    }
    
    /**
     * Function to check if the given user has archived this message
     * 
     * @param User $user
     * @return boolean
     */
    public function isMessageArchived(User $user) 
    {
        if ($this->usersArchived->contains($user)) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Add usersArchived
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersArchived
     * @return Message
     */
    public function addUsersArchived(\IMDC\TerpTubeBundle\Entity\User $usersArchived)
    {
        $this->usersArchived[] = $usersArchived;
    
        return $this;
    }

    /**
     * Remove usersArchived
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersArchived
     */
    public function removeUsersArchived(\IMDC\TerpTubeBundle\Entity\User $usersArchived)
    {
        $this->usersArchived->removeElement($usersArchived);
    }

    /**
     * Get usersArchived
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersArchived()
    {
        return $this->usersArchived;
    }


    /**
     * Function to check if the given user has deleted this message
     *
     * @param User $user
     * @return boolean
     */
    public function isMessageDeleted(User $user)
    {
        if ($this->usersDeleted->contains($user)) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Add usersDeleted
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersDeleted
     * @return Message
     */
    public function addUsersDeleted(\IMDC\TerpTubeBundle\Entity\User $usersDeleted)
    {
        $this->usersDeleted[] = $usersDeleted;
    
        return $this;
    }

    /**
     * Remove usersDeleted
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $usersDeleted
     */
    public function removeUsersDeleted(\IMDC\TerpTubeBundle\Entity\User $usersDeleted)
    {
        $this->usersDeleted->removeElement($usersDeleted);
    }

    /**
     * Get usersDeleted
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersDeleted()
    {
        return $this->usersDeleted;
    }
}