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
    private $attachedMedia;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recipients    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attachedMedia = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function setSentValueToNow()
    {
        $this->sentDate = new \DateTime('NOW');
        return $this;
    }
    
    /**
     * 
     * @param User $user
     * @return boolean
     */
    public function isMessageRead(User $user) 
    {
        if ($user->getReadMessages()->contains($this)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Add attachedMedia
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $attachedMedia
     * @return Message
     */
    public function addAttachedMedia(\IMDC\TerpTubeBundle\Entity\Media $attachedMedia)
    {
        $this->attachedMedia[] = $attachedMedia;
    
        return $this;
    }

    /**
     * Remove attachedMedia
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $attachedMedia
     */
    public function removeAttachedMedia(\IMDC\TerpTubeBundle\Entity\Media $attachedMedia)
    {
        $this->attachedMedia->removeElement($attachedMedia);
    }

    /**
     * Get attachedMedia
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttachedMedia()
    {
        return $this->attachedMedia;
    }
}