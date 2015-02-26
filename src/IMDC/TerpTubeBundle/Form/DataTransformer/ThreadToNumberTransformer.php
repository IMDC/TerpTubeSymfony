<?php
namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\Thread;

class ThreadToNumberTransformer implements DataTransformerInterface
{
    
    private $om;
    
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }
    
    public function transform($thread)
    {
        if (null === $thread) {
            return "";
        }
        
        return $thread->getId();
    }
    
    /**
     * Transforms a string (number) to an object (thread)
     * 
     * @param string $number
     * @throws TransformationFailedException
     * @return NULL|Thread
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }
        
        $thread = $this->om->getRepository('IMDCTerpTubeBundle:Thread')
                        ->findOneBy(array('id' => $number));
        
        if (null === $thread) {
            throw new TransformationFailedException(sprintf(
                    'A Thread with id "%s" does not exist!',
                    $number));
        }
        
        return $thread;
    }
}