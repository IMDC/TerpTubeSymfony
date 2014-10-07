<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
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
    private $accessProvider;
    private $objectIdentity;
    //private $acl;

    public function __construct(EntityManager $entityManager, AccessProvider $accessProvider, AccessObjectIdentity $objectIdentity)
    {
        $this->entityManager = $entityManager;
        $this->accessProvider = $accessProvider;
        $this->objectIdentity = $objectIdentity;
    }

    /*public function loadAcl()
    {
        $this->acl = null;
        try {
            $this->acl = $this->accessProvider->findAcl($this->objectIdentity->getObjectIdentity());
        } catch (AclNotFoundException $ex) {
            $this->acl = $this->accessProvider->createAcl($this->objectIdentity->getObjectIdentity());
        }
    }*/

    /*public function updateAcl()
    {
        $this->accessProvider->updateAcl($this->acl);
    }*/

    /*public function deleteAcl()
    {
        $this->accessProvider->deleteAcl($this->objectIdentity->getObjectIdentity());
    }*/

    public function insertEntries(SecurityIdentityInterface $securityIdentity)
    {
        $acl = $this->accessProvider->getAcl();
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);

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
                    $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);
                }

                break;
            case AccessType::TYPE_GROUP:
                $securityIdentities = $this->objectIdentity->getSecurityIdentities();
                $securityIdentity = $securityIdentities[0];

                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);

                break;
        }
    }

    public function updateEntries()
    {

    }

    public function isGranted(UserInterface $user)
    {
        $acl = $this->accessProvider->getAcl();

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
                // handled by AclVoter
                return false;
            case AccessType::TYPE_GROUP:
                $aces = $acl->getObjectAces();
                if (!$aces) {
                    throw new NoAceFoundException();
                }

                $securityIdentities = $this->objectIdentity->getSecurityIdentities();
                $securityIdentity = $securityIdentities[0];

                foreach ($aces as $ace) {
                    $sid = null;
                    if ($ace->getSecurityIdentity() instanceof RoleSecurityIdentity) {
                        $sid = GroupSecurityIdentity::fromSecurityIdentity($ace->getSecurityIdentity());
                    }

                    if ($sid && $sid->equals($securityIdentity)) {
                        $group = $this->entityManager->getRepository('IMDCTerpTubeBundle:UserGroup')->findByName($sid->getName());
                        if ($group && $group->getMembers()->contains($user)) {
                            return true;
                        }
                    }
                }

                return false;
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
    }
}
