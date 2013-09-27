<?php
namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\User;

class UserToStringTransformer implements DataTransformerInterface
{
    /**
     * 
     * @var ObjectManager
     */
    private $om;
    
    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (User) to a String (username)
     * @param User|null $user
     * @return string
     */
    public function transform($user)
    {
    	if (null === $user) {
    		return "";
    	}

    	return $user->getUsername();
    }
    
    /**
     * Transforms a string (username) to an object (user)
     * @param string username
     * 
     * @return User|null
     * @throws TransformationFailedException if object (user) not found
     */
    public function reverseTransform($username)
    {
    	if (!$username) {
    		return null;
    	}
    	
    	$user = $this->om->getRepository('IMDCTerpTubeBundle:User')
    						->findOneBy(array('username' => $username));
    	
    	if (null===$user) {
    		throw new TransformationFailedException(sprintf(
    				'A user with name "%s" was not found',
    				$username
    				));
    	}
    	
    	return $user;
    }
}