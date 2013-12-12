<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerptubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\IMDCTerpTubeBundle;

/**
 * MediaRepository
 *
 */
class MediaRepository extends EntityRepository
{
    public function findAllMediaCreatedByUser(User $user)
    {
        return $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Media m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE u.id = :uid
                    AND m.owner = u.id
                    ')
                ->setParameter('uid', $user->getId())
        ->getResult();
    }
    
}
