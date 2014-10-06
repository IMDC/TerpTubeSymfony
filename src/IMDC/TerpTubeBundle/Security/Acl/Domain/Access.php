<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class Access
 * @package IMDC\TerpTubeBundle\Security\Acl\Domain
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class Access
{
    private $entityManager;
    private $aclProvider;
    private $objectIdentity;
    private $acl;

    public function __construct(EntityManager $entityManager, MutableAclProviderInterface $aclProvider, AccessObjectIdentity $objectIdentity)
    {
        $this->entityManager = $entityManager;
        $this->aclProvider = $aclProvider;
        $this->objectIdentity = $objectIdentity;
    }

    public function loadAcl()
    {
        $this->acl = null;
        try {
            $this->acl = $this->aclProvider->findAcl($this->objectIdentity->getObjectIdentity());
        } catch (AclNotFoundException $ex) {
            $this->acl = $this->aclProvider->createAcl($this->objectIdentity->getObjectIdentity());
        }
    }

    public function updateAcl()
    {
        $this->aclProvider->updateAcl($this->acl);
    }

    public function deleteAcl()
    {
        $this->aclProvider->deleteAcl($this->objectIdentity->getObjectIdentity());
    }

    public function insertEntries(SecurityIdentityInterface $securityIdentity)
    {
        $this->loadAcl();

        $this->acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);

        switch ($this->objectIdentity->getAccessType()->getId()) {
            case AccessType::TYPE_PUBLIC:
            case AccessType::TYPE_LINK_ONLY:
            case AccessType::TYPE_REGISTERED_USERS:
            case AccessType::TYPE_PRIVATE:
                // no aces needed
                break;
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
                foreach ($this->objectIdentity->getSecurityIdentities() as $securityIdentity) {
                    $this->acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);
                }

                break;
            case AccessType::TYPE_GROUP:
                $securityIdentities = $this->objectIdentity->getSecurityIdentities();
                $securityIdentity = $securityIdentities[0];

                $this->acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);

                break;
        }
    }

    public function isGranted(UserInterface $user)
    {
        switch ($this->objectIdentity->getAccessType()->getId()) {
            case AccessType::TYPE_PUBLIC:
            case AccessType::TYPE_LINK_ONLY:
                return true;
            case AccessType::TYPE_REGISTERED_USERS:
                // is the user logged in?
                if ($user instanceof UserInterface) {
                    return true;
                }
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
            case AccessType::TYPE_GROUP:
            case AccessType::TYPE_PRIVATE:
                // handled by AclVoter
                return false;
            /*case AccessType::TYPE_USERS:
                foreach ($this->entries as $entry) {
                    if ($entry->getAllowedObjectIdentity() === $user->getId())
                        return true;
                }

                break;
            case AccessType::TYPE_FRIENDS:
                if ($objectOwner->getId() === $user->getId() ||
                    $objectOwner->getFriendsList()->contains($user))
                    return true;

                break;
            case AccessType::TYPE_GROUP:
                if (count($this->entries) !== 1) {
                    throw new \InvalidArgumentException('At least/most one group allowed');
                }

                $entry = $this->entries[0];
                $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->find($entry->getAllowedObjectIdentity());
                if (!$group) {
                    throw new \Exception('group not found');
                }

                if ($group->getMembers()->contains($user))
                    return true;

                break;
            case AccessType::TYPE_PRIVATE:
                if ($objectOwner->getId() === $user->getId())
                    return true;

                break;*/
        }

        return false;
    }
}
