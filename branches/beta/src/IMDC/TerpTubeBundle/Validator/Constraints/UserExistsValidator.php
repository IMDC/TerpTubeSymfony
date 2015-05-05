<?php

namespace IMDC\TerpTubeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class UserExistsValidator extends ConstraintValidator
{
	private $securityContext;
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	public function validate($value, Constraint $constraint)
	{
		// var_dump($constraint);
		if (empty ( $value ))
		{
			return true;
		}
		$usernamesArray = $value;
		$invalidUsers = array ();
		foreach ( $usernamesArray as $userToValidate )
		{
			$username = $userToValidate->getUsername();
			$user = $this->entityManager->getRepository ( 'IMDCTerpTubeBundle:User' )->findOneBy ( array (
					'username' => $username 
			) );
			if ($user == null)
			{
				$invalidUsers [] = $username;
			}
		}
		if (count ( $invalidUsers ) > 0)
		{
			$usernames = implode ( ", ", $invalidUsers );
			if (count ( $invalidUsers ) > 1)
			{
				if ($constraint->propertyPath != null)
					$this->context->addViolationAt ( $constraint->propertyPath, $constraint->multipleMessage, array (
							'%user_names%' => $usernames 
					) );
				else
					$this->context->addViolation ( $constraint->multipleMessage, array (
							'%user_names%' => $usernames 
					) );
			}
			else
			{
				if ($constraint->propertyPath != null)
					$this->context->addViolationAt ( $constraint->propertyPath, $constraint->singleMessage, array (
							'%user_name%' => $usernames 
					) );
				else
					$this->context->addViolation ( $constraint->singleMessage, array (
							'%user_name%' => $usernames 
					) );
			}
			return false;
		}
	}
}
