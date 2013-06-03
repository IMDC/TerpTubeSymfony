<?php
// src/IMDC\TerpTubeBundle/Entity/User.php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{
    protected $id;
	protected $firstName;
	protected $lastName;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
	
	public function getFirstName()
	{
		return $this->firstName;
	}
	
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}
	
	public function getLastName()
	{
		return $this->lastName;
	}
	
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}
	
	
}