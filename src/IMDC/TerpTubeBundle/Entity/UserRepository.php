<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Straight SQL statement to modify the noreply user's id
     * so that the id is 0.
     * 
     * @param unknown $adminemail
     * @return number the number of affected rows
     */
    public function modifyNoReplyUser($adminemail) {
        
        $stmt = $this->getEntityManager()->getConnection()
                  ->prepare("UPDATE fos_user set id=0 where email=? LIMIT 1");
        
        $stmt->bindParam(1, $adminemail);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    public function getMostRecentInboxMessages($user, $limit=30) 
    {
    	$query = $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE :uid MEMBER OF m.recipients
    	            and m.id not in (select g.id from IMDCTerpTubeBundle:User r join r.archivedMessages g)
    	            and m.id not in (select d.id from IMDCTerpTubeBundle:User e join e.deletedMessages d)
    	            ORDER BY m.sentDate DESC
                ')
                ->setParameter('uid', $user->getId());

    	$query->setMaxResults($limit);
    	
        return $query->getResult();
    	
    }
    
    public function getMostRecentSentMessages($user, $limit=30)
    {
        $query = $this->getEntityManager()->createQuery('
                    SELECT m
                    FROM IMDCTerpTubeBundle:Message m
                    JOIN IMDCTerpTubeBundle:User u
                    WHERE :uid = m.owner
    	            and m.id not in (select d.id from IMDCTerpTubeBundle:User e join e.deletedMessages d)
    	            ORDER BY m.sentDate DESC
                ')
                ->setParameter('uid', $user->getId());
        
        $query->setMaxResults($limit);
         
        return $query->getResult();
    }
    
    public function getSentMessagesCount($user) 
    {
        $query = $this->getEntityManager()->createQuery('
                SELECT count(m)
                FROM IMDCTerpTubeBundle:Message m
                JOIN IMDCTerpTubeBundle:User u
                WHERE m.owner = u.id 
                AND m.owner = :uid
                and m.id not in (select d.id from IMDCTerpTubeBundle:User r join r.deletedMessages d)
                ')
                ->setParameter('uid', $user->getId());
        
        return $query->getResult();
    }
    
    public function findAllPublicListedMembers($firstresult, $lastresult) 
    {
        $query = $this->getEntityManager()->createQuery('
                SELECT u
                FROM IMDCTerpTubeBundle:User u
                JOIN IMDCTerpTubeBundle:UserProfile p
                WHERE p.profileVisibleToPublic = true
                AND u.username <> :uname
                ORDER BY u.joinDate DESC
        ')
        ->setParameter('uname' , 'noreply');
        $query->setFirstResult($firstresult);
        $query->setMaxResults($lastresult);
        
        return $query->getResult();
    }
}