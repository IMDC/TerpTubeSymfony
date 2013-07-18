<?php
// src/IMDC\TerpTubeBundle/Entity/User.php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{
    protected $id;
	protected $profile;
// 	protected $lastName;
	
	protected $messages;

    public function __construct()
    {
        parent::__construct();
     //   $profile = new UserProfile();
        // your own logic
    }
	
// 	public function getFirstName()
// 	{
// 		return $this->firstName;
// 	}
	
// 	public function setFirstName($firstName)
// 	{
// 		$this->firstName = $firstName;
// 	}
	
// 	public function getLastName()
// 	{
// 		return $this->lastName;
// 	}
	
// 	public function setLastName($lastName)
// 	{
// 		$this->lastName = $lastName;
// 	}
	
	

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
     * Add messages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $messages
     * @return User
     */
    public function addMessage(\IMDC\TerpTubeBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;
    
        return $this;
    }

    /**
     * Remove messages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Message $messages
     */
    public function removeMessage(\IMDC\TerpTubeBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }
}