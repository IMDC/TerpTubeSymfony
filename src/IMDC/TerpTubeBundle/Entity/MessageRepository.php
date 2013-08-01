<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use IMDC\TerpTubeBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
    
    /**
     * 
     * @param User $user
     * @param int $number
     */
    public function findAllReceivedInboxMessagesForUser(User $user, $number=30)
    {
/*         $dql = "SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE :uid MEMBER OF m.recipients
                    AND :uid NOT MEMBER of m.usersDeleted
                    AND :uid NOT MEMBER of m.usersArchived
                    ORDER BY m.sentDate DESC";
        $query = $this->getEntityManager()->createQuery($dql)
                       ->setParameter('uid', $user->gedId())
                       ->setFirstResult(0)
                       ->setMaxResults($number);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        $c = count($paginator); */
        
        return $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE :uid MEMBER OF m.recipients
                    AND :uid NOT MEMBER of m.usersDeleted
                    AND :uid NOT MEMBER of m.usersArchived
                    ORDER BY m.sentDate DESC
                ')
                    ->setParameter('uid', $user->getId())
                    ->getResult();
    }
}
