<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group as BaseGroup;

/**
 * UserGroup
 */
class UserGroup extends BaseGroup
{
    /**
     * @var integer
     */
    protected $id;
    
    /**
     * @var \DateTime
     */
    private $dateCreated;
    
    /**
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $userFounder;

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
    
    public function setDateCreatedToNow()
    {
        $this->dateCreated = new \DateTime('NOW');
        return $this;
    }
}