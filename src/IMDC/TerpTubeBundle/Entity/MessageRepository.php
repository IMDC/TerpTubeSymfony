<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\User;

/**
 * MessageRepository
 *
 */
class MessageRepository extends EntityRepository
{
    public function findAllSentMessagesForUser(User $user)
    {
        return $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    WHERE m.owner = :oid
                    ORDER BY m.sentDate ASC')
                ->setParameter('oid', $user->getId())
        ->getResult();
    }
    
    public function findAllReceivedMessagesForUser(User $user)
    {
        return $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE :uid MEMBER OF m.recipients
                    ORDER BY m.sentDate DESC
                ')
                ->setParameter('uid', $user->getId())
        ->getResult();
    }
}
