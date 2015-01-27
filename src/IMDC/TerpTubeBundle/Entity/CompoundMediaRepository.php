<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerptubeBundle\Entity\Media;
use IMDC\TerptubeBundle\Entity\CompoundMedia;
use IMDC\TerpTubeBundle\IMDCTerpTubeBundle;

/**
 * MediaRepository
 * @deprecated
 */
class CompoundMediaRepository extends EntityRepository { // TODO delete
	public function findAllInterpretationsCreatedByUser(User $user) {
		return $this->getEntityManager ()->createQuery ( '
                    SELECT c
                    FROM IMDCTerpTubeBundle:User u
					JOIN IMDCTerpTubeBundle:Media m WITH m.owner = u.id
					JOIN IMDCTerpTubeBundle:CompoundMedia c WITH m.id = c.target
					WHERE u.id = :uid
                    ' )->setParameter ( 'uid', $user->getId () )->getResult ();
	}
	public function findAllSourceVideosCreatedByUser(User $user) {
		return $this->getEntityManager ()->createQuery ( '
                    SELECT c
                    FROM IMDCTerpTubeBundle:Media m
                    JOIN IMDCTerpTubeBundle:User u
    				JOIN IMDCTerpTubeBundle:CompoundMedia c
                    WHERE u.id = :uid
                    AND m.owner = u.id
    				AND m.id = c.source_id
                    ' )->setParameter ( 'uid', $user->getId () )->getResult ();
	}
}
