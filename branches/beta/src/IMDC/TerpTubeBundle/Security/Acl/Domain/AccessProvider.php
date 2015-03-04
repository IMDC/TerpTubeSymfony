<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessData;
use IMDC\TerpTubeBundle\Entity\AccessEntry;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Security\Acl\AccessDataToFormDataTransformer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
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
    private $accessData;

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
     * @param FormInterface $form
     * @return Access
     */
    public function createAccess(AccessObjectIdentity $objectIdentity, FormInterface $form = null)
    {
        $this->loadAcl($objectIdentity);
        $this->loadAccessData($objectIdentity, $form);

        return new Access($this->entityManager, $this, $objectIdentity);
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     * @return Access
     * @deprecated
     */
    public function getAccess(AccessObjectIdentity $objectIdentity)
    {
        return $this->createAccess($objectIdentity);
    }

    public function updateAccess()
    {
        $this->aclProvider->updateAcl($this->acl);

        $this->entityManager->persist($this->accessData);
        $this->entityManager->flush();
    }

    /**
     * @param AccessObjectIdentity $objectIdentity
     */
    public function deleteAccess(AccessObjectIdentity $objectIdentity)
    {
        $this->aclProvider->deleteAcl($objectIdentity->getObjectIdentity());

        $this->entityManager->remove($this->accessData);
        $this->entityManager->flush();
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

    public function loadAccessData(AccessObjectIdentity $accessObjectIdentity, FormInterface $form = null)
    {
        $objectIdentifier = $accessObjectIdentity->getObjectIdentity()->getIdentifier();

        //TODO this could be replaced by reversing the security identities for the needed data
        $this->accessData = $this->entityManager
            ->getRepository('IMDCTerpTubeBundle:AccessData')
            ->findOneBy(array('objectIdentifier' => $objectIdentifier));

        if (!$this->accessData) {
            $this->accessData = new AccessData();
            $this->accessData->setObjectIdentifier($objectIdentifier);
        }

        $accessType = $accessObjectIdentity->getAccessType();

        if ($form) {
            $transformer = new AccessDataToFormDataTransformer($accessType, $this->entityManager, $this->accessData);
            $transformer->reverseTransform($form);
        }
    }

    /**
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @return AccessData
     */
    public function getAccessData()
    {
        return $this->accessData;
    }

    /**
     * @param AccessObjectIdentity $accessObjectIdentity
     * @param $object
     * @return array
     */
    public function setSecurityIdentities(AccessObjectIdentity $accessObjectIdentity, $object) //TODO revise if group is moved into access data
    {
        $accessType = $accessObjectIdentity->getAccessType();
        $securityIdentities = array();

        if ($accessType->getId() == AccessType::TYPE_USERS) {
            $transformer = new AccessDataToFormDataTransformer($accessType, $this->entityManager);
            $data = $transformer->transform($this->accessData);

            if (is_array($data['users'])) {
                foreach ($data['users'] as $user) {
                    $securityIdentities[] = UserSecurityIdentity::fromAccount($user);
                }
            }
        }
        if ($accessType->getId() == AccessType::TYPE_GROUP && $object instanceof Forum) {
            $securityIdentities[] = GroupSecurityIdentity::fromGroup($object->getGroup());
        }

        $accessObjectIdentity->setSecurityIdentities($securityIdentities);
    }
}
