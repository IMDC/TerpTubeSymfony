<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invitation
 */
class Invitation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var boolean
     */
    private $isAccepted;

    /**
     * @var \DateTime
     */
    private $dateAccepted;

    /**
     * @var boolean
     */
    private $isCancelled;

    /**
     * @var \DateTime
     */
    private $dateCancelled;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\InvitationType
     */
    private $type;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $creator;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $recipient;
    
    /**
     * @var boolean
     */
    private $isDeclined;
    
    /**
     * @var \DateTime
     */
    private $dateDeclined;
    

    /**
     * Set default values for properties of the Invitation 
     * when it is first created, most importantly the dateCreated value
     */
    public function __construct()
    {
        $this->setIsAccepted(false);
        $this->setIsCancelled(false);
        $this->setIsDeclined(false);
        $this->setDateCreated(new \DateTime('now'));
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
     * @return Invitation
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
     * Set isAccepted
     *
     * @param boolean $isAccepted
     * @return Invitation
     */
    public function setIsAccepted($isAccepted)
    {
        $this->isAccepted = $isAccepted;
    
        return $this;
    }

    /**
     * Get isAccepted
     *
     * @return boolean 
     */
    public function getIsAccepted()
    {
        return $this->isAccepted;
    }

    /**
     * Set dateAccepted
     *
     * @param \DateTime $dateAccepted
     * @return Invitation
     */
    public function setDateAccepted($dateAccepted)
    {
        $this->dateAccepted = $dateAccepted;
    
        return $this;
    }

    /**
     * Get dateAccepted
     *
     * @return \DateTime 
     */
    public function getDateAccepted()
    {
        return $this->dateAccepted;
    }

    /**
     * Set isCancelled
     *
     * @param boolean $isCancelled
     * @return Invitation
     */
    public function setIsCancelled($isCancelled)
    {
        $this->isCancelled = $isCancelled;
    
        return $this;
    }

    /**
     * Get isCancelled
     *
     * @return boolean 
     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
    }

    /**
     * Set dateCancelled
     *
     * @param \DateTime $dateCancelled
     * @return Invitation
     */
    public function setDateCancelled($dateCancelled)
    {
        $this->dateCancelled = $dateCancelled;
    
        return $this;
    }

    /**
     * Get dateCancelled
     *
     * @return \DateTime 
     */
    public function getDateCancelled()
    {
        return $this->dateCancelled;
    }

    /**
     * Set type
     *
     * @param \IMDC\TerpTubeBundle\Entity\InvitationType $type
     * @return Invitation
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return \IMDC\TerpTubeBundle\Entity\InvitationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creator
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $creator
     * @return Invitation
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
     * Set recipient
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $recipient
     * @return Invitation
     */
    public function setRecipient(\IMDC\TerpTubeBundle\Entity\User $recipient = null)
    {
        $this->recipient = $recipient;
    
        return $this;
    }

    /**
     * Get recipient
     *
     * @return \IMDC\TerpTubeBundle\Entity\User 
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set isDeclined
     *
     * @param boolean $isDeclined
     * @return Invitation
     */
    public function setIsDeclined($isDeclined)
    {
        $this->isDeclined = $isDeclined;
    
        return $this;
    }

    /**
     * Get isDeclined
     *
     * @return boolean 
     */
    public function getIsDeclined()
    {
        return $this->isDeclined;
    }

    /**
     * Set dateDeclined
     *
     * @param \DateTime $dateDeclined
     * @return Invitation
     */
    public function setDateDeclined($dateDeclined)
    {
        $this->dateDeclined = $dateDeclined;
    
        return $this;
    }

    /**
     * Get dateDeclined
     *
     * @return \DateTime 
     */
    public function getDateDeclined()
    {
        return $this->dateDeclined;
    }
    /**
     * @var array
     */
    private $data;


    /**
     * Set data
     *
     * @param array $data
     * @return Invitation
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }
}
