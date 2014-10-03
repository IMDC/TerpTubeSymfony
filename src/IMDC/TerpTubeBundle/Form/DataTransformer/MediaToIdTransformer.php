<?php

namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class MediaToIdTransformer implements DataTransformerInterface
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param Media $media
     * @return int
     */
    public function transform($media)
    {
        if (!$media)
            return -1;

        return $media->getId();
    }

    /**
     * @param int $id
     * @return null|Media
     */
    public function reverseTransform($id)
    {
        if (!$id)
            return null;

        $media = $this->om->getRepository('IMDCTerpTubeBundle:Media')->find($id);

        // commented out allow nulls
        /*if (!$media) {
            throw new TransformationFailedException(sprintf(
                'media id %d not found', $id
            ));
        }*/

        return $media;
    }
}
