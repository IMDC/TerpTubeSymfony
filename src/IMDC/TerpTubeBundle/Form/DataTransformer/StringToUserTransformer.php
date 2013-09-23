<?php
namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\User;

class StringToUserTransformer implements DataTransformerInterface
{
    
    private $om;
    
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($user)
    {
    	if (null === $user) {
    		return "";
    	}
    	
    	return $user->getId();
    }
    
    public function reverseTransform($string)
    {
    	if (!$string) {
    		return null;
    	}
    	
    	$user = $this->om->getRepository('IMDCTerpTubeBundle:User')
    						->findOneBy(array('username' => $string));
    	
    	if (null===$user) {
    		throw new TransformationFailedException(sprintf(
    				'A user with name "%s" was not found',
    				$string
    				));
    	}
    	
    	return $user;
    }
}