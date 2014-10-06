<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessEntry;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;

/**
 * Class AccessProvider
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessProvider
{
    private $entityManager;
    private $aclProvider;

    public function __construct(EntityManager $entityManager, MutableAclProvider $aclProvider)
    {
        $this->entityManager = $entityManager;
        $this->aclProvider = $aclProvider;
    }

    /*private function createEntry($allowedObjectIdentity)
    {
        $entry = new AccessEntry();
        $entry->setAccessType($this->accessType);
        $entry->setObjectIdentity($this->objectIdentity);
        $entry->setObjectClass($this->objectClass);
        $entry->setAllowedObjectIdentity($allowedObjectIdentity);

        return $entry;
    }*/

    /*private function loadEntries()
    {
        $qb = $this->entityManager->getRepository('IMDCTerpTubeBundle:AccessEntry')->createQueryBuilder('e');
        $this->entries = $qb->leftJoin('e.access_type', 't')
            ->where('e.object_identity = :objectIdentity')
            ->andWhere('e.object_class = :objectClass')
            ->andWhere('t.id = :accessTypeId')
            ->setParameters(array(
                'objectIdentity' => $this->objectIdentity,
                'objectClass' => $this->objectClass,
                'accessTypeId' => $this->accessType->getId()))
            ->getQuery()->getResult();
    }*/

    public function createAccess(AccessObjectIdentity $objectIdentity)
    {
        return new Access($this->entityManager, $this->aclProvider, $objectIdentity);
    }

    public function getAccess(AccessObjectIdentity $objectIdentity)
    {
        //$this->loadEntries();

        $access = $this->createAccess($objectIdentity);
        $access->loadAcl();

        return $access;
    }

    public function deleteAccess(AccessObjectIdentity $objectIdentity)
    {
        /*$this->loadEntries();

        foreach ($this->entries as $entry)
            $this->entityManager->remove($entry);

        $this->entityManager->flush();*/

        $access = $this->createAccess($objectIdentity);
        $access->deleteAcl();
    }

    public function updateAccess(Access $access)
    {
        /*switch ($access->getObjectIdentity()->getAccessType()->getId()) {
            case AccessType::TYPE_PUBLIC:
            case AccessType::TYPE_LINK_ONLY:
            case AccessType::TYPE_REGISTERED_USERS:
            case AccessType::TYPE_PRIVATE:
                break;
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
                break;
            case AccessType::TYPE_GROUP:
                $entry = $this->createEntry($this->objectIdentity->getGroup()->getId());
                $this->entityManager->persist($entry);
                $this->entityManager->flush();

                break;
        }*/

        $access->updateAcl();
    }
}
