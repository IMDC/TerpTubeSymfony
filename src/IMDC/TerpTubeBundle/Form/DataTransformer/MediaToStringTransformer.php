<?php
namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use IMDC\TerpTubeBundle\Entity\Media;

class MediaToStringTransformer implements DataTransformerInterface
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
     * Transforms an object (media) to a string (number)
     * @param Issue|null $issue
     * @return string
     */
    public function transform($media)
    {
        if (null === $media) {
            return "";
        }
        
        return $media->getId();
    }
    
    /**
     * Transforms a string (number) to an object (media)
     * 
     * @param string $number
     * @return Media|null
     * @throws TransformationFailedException if object (media) is not found
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }
        
        $media = $this->om->getRepository('IMDCTerpTubeBundle:Media')
                            ->findOneBy(array('id' => $number));
        
        if (null === $media) {
            throw new TransformationFailedException(sprintf(
                    'A Media object with id "%s" does not exist!',
                    $number
                    ));
        }
        
        return $media;
    }
}