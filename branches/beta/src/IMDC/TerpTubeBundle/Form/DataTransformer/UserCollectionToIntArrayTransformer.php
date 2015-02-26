<?php

namespace IMDC\TerpTubeBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserCollectionToIntArrayTransformer implements DataTransformerInterface
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

        foreach ($collection as $user) {
            $ids[] = $user->getId();
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

        $qb = $this->om->getRepository('IMDCTerpTubeBundle:User')->createQueryBuilder('u');
        $qb->where($qb->expr()->in('u.id', $ids));

        $collection = $qb->getQuery()->getResult();

        return $collection;
    }
}
