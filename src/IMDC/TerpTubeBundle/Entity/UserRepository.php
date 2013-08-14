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
}