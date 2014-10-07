<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\EntryInterface;
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

        $accessType = $this->objectIdentity->getAccessType()->getId();
        switch ($accessType) {
            case AccessType::TYPE_PUBLIC:
            case AccessType::TYPE_LINK_ONLY:
            case AccessType::TYPE_REGISTERED_USERS:
            case AccessType::TYPE_PRIVATE:
                // no other aces needed
                break;
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
            case AccessType::TYPE_GROUP:
                foreach ($this->objectIdentity->getSecurityIdentities() as $securityIdentity) {
                    if ($accessType == AccessType::TYPE_GROUP && $securityIdentity instanceof GroupSecurityIdentity)
                        $securityIdentity = $securityIdentity->toRoleSecurityIdentity();

                    $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);
                }

                break;
        }
    }

    public function updateEntries(SecurityIdentityInterface $securityIdentity)
    {
        $acl = $this->accessProvider->getAcl();

        $aces = $acl->getObjectAces();
        if (!$aces) {
            // no aces to update
            return;
        }

        $accessType = $this->objectIdentity->getAccessType()->getId();
        switch ($accessType) {
            case AccessType::TYPE_PUBLIC:
            case AccessType::TYPE_LINK_ONLY:
            case AccessType::TYPE_REGISTERED_USERS:
                // no aces needed
                break;
            case AccessType::TYPE_PRIVATE:
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
            case AccessType::TYPE_GROUP:
                $securityIdentities = $this->objectIdentity->getSecurityIdentities();
                $securityIdentities[] = $securityIdentity; // current or new owner
                $foundSids = array();

                $sidExists = function (SecurityIdentityInterface $sid) use ($accessType, &$securityIdentities, &$foundSids) {
                    foreach ($securityIdentities as $securityIdentity) {
                        $sid2 = null;
                        if ($accessType == AccessType::TYPE_GROUP && $sid instanceof RoleSecurityIdentity)
                            $sid2 = GroupSecurityIdentity::fromRoleSecurityIdentity($sid);
                        if (!$sid2)
                            $sid2 = $sid;

                        if ($securityIdentity->equals($sid2)) {
                            // already found (duplicate)?
                            foreach ($foundSids as $foundSid) {
                                if ($foundSid->equals($sid2))
                                    return false;
                            }
                            $foundSids[] = $sid2;
                            return true;
                        }
                    }
                    return false;
                };
                $aceExists = function (SecurityIdentityInterface $sid) use (&$foundSids) {
                    foreach ($foundSids as $foundSid) {
                        if ($foundSid->equals($sid))
                            return true;
                    }
                    return false;
                };

                $delAces = 0;
                for ($a=0; $a<count($aces); $a++) {
                    $ace = $aces[$a];
                    if (!$sidExists($ace->getSecurityIdentity())) {
                        $acl->deleteObjectAce($a-$delAces++);
                    }
                }

                foreach ($securityIdentities as $sid) {
                    if (!$aceExists($sid)) {
                        if ($accessType == AccessType::TYPE_GROUP && $sid instanceof GroupSecurityIdentity)
                            $sid = $sid->toRoleSecurityIdentity();

                        $acl->insertObjectAce($sid, $sid->equals($securityIdentity) ? MaskBuilder::MASK_OWNER : MaskBuilder::MASK_VIEW);
                    }
                }

                break;
        }
    }

    public function isGranted(UserInterface $user)
    {
        $acl = $this->accessProvider->getAcl();

        switch ($this->objectIdentity->getAccessType()->getId()) {
            case AccessType::TYPE_PUBLIC:
                // nothing to check
                return true;
            case AccessType::TYPE_LINK_ONLY:
                //TODO check request url
                return true;
            case AccessType::TYPE_REGISTERED_USERS:
                // is the user logged in?
                if ($user instanceof UserInterface) {
                    return true;
                }
                return false;
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_FRIENDS:
                // handled by AclVoter
                return false;
            case AccessType::TYPE_GROUP:
                $aces = $acl->getObjectAces();
                if (!$aces) {
                    //throw new NoAceFoundException(); //FIXME revise this
                    return false;
                }

                $securityIdentities = $this->objectIdentity->getSecurityIdentities();
                $securityIdentity = $securityIdentities[0];

                foreach ($aces as $ace) {
                    if (!$ace->getSecurityIdentity() instanceof RoleSecurityIdentity)
                        continue;

                    $sid = GroupSecurityIdentity::fromRoleSecurityIdentity($ace->getSecurityIdentity());
                    if ($sid && $sid->equals($securityIdentity)) {
                        $group = $this->entityManager->find('IMDCTerpTubeBundle:UserGroup', $sid->getId());
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
