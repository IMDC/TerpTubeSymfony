<?php

namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MediaCollectionToIdArrayTransformer implements DataTransformerInterface
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param Collection $collection
     * @return int[]
     */
    public function transform($collection)
    {
        $ids = array();

        if (!$collection)
            return $ids;

        foreach ($collection as $media) {
            $ids[] = $media->getId();
        }

        return $ids;
    }

    /**
     * @param int[] $ids
     * @return array
     */
    public function reverseTransform($ids)
    {
        if (!$ids)
            return null;

        $qb = $this->om->getRepository('IMDCTerpTubeBundle:Media')->createQueryBuilder('m');
        $qb->where($qb->expr()->in('m.id', $ids));

        $collection = $qb->getQuery()->getResult();

        return $collection;
    }
}
