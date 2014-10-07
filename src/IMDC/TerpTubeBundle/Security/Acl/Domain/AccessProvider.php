<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessEntry;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;

/**
 * Class AccessProvider
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessProvider
{
    private $entityManager;
    private $aclProvider;
    private $acl;

    public function __construct(EntityManager $entityManager, MutableAclProviderInterface $aclProvider)
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
        $this->loadAcl($objectIdentity);

        return new Access($this->entityManager, $this, $objectIdentity);
    }

    public function getAccess(AccessObjectIdentity $objectIdentity)
    {
        //$this->loadEntries();

        return $this->createAccess($objectIdentity);
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

        $this->aclProvider->updateAcl($this->acl);
    }

    public function deleteAccess(AccessObjectIdentity $objectIdentity)
    {
        /*$this->loadEntries();

        foreach ($this->entries as $entry)
            $this->entityManager->remove($entry);

        $this->entityManager->flush();*/

        //$access = $this->createAccess($objectIdentity);
        //$access->deleteAcl();

        $this->aclProvider->deleteAcl($objectIdentity->getObjectIdentity());
    }

    public function loadAcl(AccessObjectIdentity $objectIdentity)
    {
        $this->acl = null;
        try {
            $this->acl = $this->aclProvider->findAcl($objectIdentity->getObjectIdentity());
        } catch (AclNotFoundException $ex) {
            $this->acl = $this->aclProvider->createAcl($objectIdentity->getObjectIdentity());
        }
    }

    /**
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }
}
