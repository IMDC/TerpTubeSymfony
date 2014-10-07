<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessEntry;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclCacheInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Class AccessProvider
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessProvider extends MutableAclProvider
{
    private $entityManager;
    private $acl;

    public function __construct(EntityManager $entityManager, PermissionGrantingStrategyInterface $permissionGrantingStrategy, array $options, AclCacheInterface $cache = null)
    {
        $this->entityManager = $entityManager;

        parent::__construct($entityManager->getConnection(), $permissionGrantingStrategy, $options, $cache);
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

        $access->updateEntries();

        $this->updateAcl($this->acl);
    }

    public function deleteAccess(AccessObjectIdentity $objectIdentity)
    {
        /*$this->loadEntries();

        foreach ($this->entries as $entry)
            $this->entityManager->remove($entry);

        $this->entityManager->flush();*/

        //$access = $this->createAccess($objectIdentity);
        //$access->deleteAcl();

        $this->deleteAcl($objectIdentity->getObjectIdentity());
    }

    private function loadAcl(AccessObjectIdentity $objectIdentity)
    {
        $this->acl = null;
        try {
            $this->acl = $this->findAcl($objectIdentity->getObjectIdentity());
        } catch (AclNotFoundException $ex) {
            $this->acl = $this->createAcl($objectIdentity->getObjectIdentity());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getInsertSecurityIdentitySql(SecurityIdentityInterface $sid)
    {
        if ($sid instanceof GroupSecurityIdentity) {
            $identifier = $sid->getClass().'-'.$sid->getName();
            $username = false;
        } else {
            return parent::getInsertSecurityIdentitySql($sid);
        }

        return sprintf(
            'INSERT INTO %s (identifier, username) VALUES (%s, %s)',
            $this->options['sid_table_name'],
            $this->connection->quote($identifier),
            $this->connection->getDatabasePlatform()->convertBooleans($username)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getSelectSecurityIdentityIdSql(SecurityIdentityInterface $sid)
    {
        if ($sid instanceof GroupSecurityIdentity) {
            $identifier = $sid->getClass().'-'.$sid->getName();
            $username = false;
        } else {
            return parent::getSelectSecurityIdentityIdSql($sid);
        }

        return sprintf(
            'SELECT id FROM %s WHERE identifier = %s AND username = %s',
            $this->options['sid_table_name'],
            $this->connection->quote($identifier),
            $this->connection->getDatabasePlatform()->convertBooleans($username)
        );
    }

    /**
     * @return mixed
     */
    public function getAcl()
    {
        return $this->acl;
    }
}
