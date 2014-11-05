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

    /**
     * @param EntityManager $entityManager
     * @param MutableAclProviderInterface $aclProvider
     */
    public function __construct(EntityManager $entityManager, MutableAclProviderInterface $aclProvider)
    {
        $this->entityManager = $entityManager;
        $this->aclProvider = $aclProvider;
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     * @return Access
     */
    public function createAccess(AccessObjectIdentity $objectIdentity)
    {
        $this->loadAcl($objectIdentity);

        return new Access($this->entityManager, $this, $objectIdentity);
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     * @return Access
     */
    public function getAccess(AccessObjectIdentity $objectIdentity)
    {
        return $this->createAccess($objectIdentity);
    }

    /**
     * @param Access $access
     */
    public function updateAccess(Access $access)
    {
        //FIXME will $access ever be used?

        $this->aclProvider->updateAcl($this->acl);
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     */
    public function deleteAccess(AccessObjectIdentity $objectIdentity)
    {
        $this->aclProvider->deleteAcl($objectIdentity->getObjectIdentity());
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     */
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
