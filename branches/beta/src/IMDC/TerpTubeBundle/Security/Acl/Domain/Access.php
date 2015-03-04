<?php

namespace IMDC\TerpTubeBundle\Security\Acl\Domain;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
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

    /**
     * @param EntityManager $entityManager
     * @param AccessProvider $accessProvider
     * @param AccessObjectIdentity $objectIdentity
     */
    public function __construct(EntityManager $entityManager, AccessProvider $accessProvider, AccessObjectIdentity $objectIdentity)
    {
        $this->entityManager = $entityManager;
        $this->accessProvider = $accessProvider;
        $this->objectIdentity = $objectIdentity;
    }

    /**
     * @param SecurityIdentityInterface $securityIdentity
     */
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
            case AccessType::TYPE_FRIENDS:
                // no other aces needed
                break;
            case AccessType::TYPE_USERS:
            case AccessType::TYPE_GROUP:
                foreach ($this->objectIdentity->getSecurityIdentities() as $securityIdentity) {
                    if ($accessType == AccessType::TYPE_GROUP && $securityIdentity instanceof GroupSecurityIdentity)
                        $securityIdentity = GroupSecurityIdentity::toRoleSecurityIdentity($securityIdentity);

                    $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);
                }

                break;
        }
    }

    /**
     * @param SecurityIdentityInterface $securityIdentity
     */
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
                for ($a = 0; $a < count($aces); $a++) {
                    $ace = $aces[$a];
                    if (!$sidExists($ace->getSecurityIdentity())) {
                        $acl->deleteObjectAce($a - $delAces++);
                    }
                }

                foreach ($securityIdentities as $sid) {
                    if (!$aceExists($sid)) {
                        if ($accessType == AccessType::TYPE_GROUP && $sid instanceof GroupSecurityIdentity)
                            $sid = GroupSecurityIdentity::toRoleSecurityIdentity($sid);

                        $acl->insertObjectAce($sid, $sid->equals($securityIdentity) ? MaskBuilder::MASK_OWNER : MaskBuilder::MASK_VIEW);
                    }
                }

                break;
        }
    }

    /**
     * @param UserInterface $user
     * @param Request $request
     * @param Router $router
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function isGranted(UserInterface $user, Request $request = null, Router $router = null)
    {
        $acl = $this->accessProvider->getAcl();

        switch ($this->objectIdentity->getAccessType()->getId()) {
            case AccessType::TYPE_PUBLIC:
                // nothing to check
                return true;
            case AccessType::TYPE_LINK_ONLY:
                if (!$request || !$router) {
                    return false;
                }

                $objectIdent = $this->objectIdentity->getObjectIdentity();
                $resourceUrl = '';

                if ($objectIdent->getType() === 'IMDC\TerpTubeBundle\Entity\Forum') {
                    $resourceUrl = $router->generate('imdc_forum_view', array(
                        'forumid' => (int)$objectIdent->getIdentifier()
                    ), UrlGenerator::ABSOLUTE_PATH); // ensure absolute paths always
                }

                if ($objectIdent->getType() === 'IMDC\TerpTubeBundle\Entity\Thread') {
                    $resourceUrl = $router->generate('imdc_thread_view', array(
                        'threadid' => (int)$objectIdent->getIdentifier()
                    ), UrlGenerator::ABSOLUTE_PATH);
                }

                // check base url with path info only as generated urls are just that
                if ($resourceUrl === $request->getBaseUrl().$request->getPathInfo()) {
                    return true;
                }

                return false;
            case AccessType::TYPE_REGISTERED_USERS:
                // is the user logged in?
                if ($user instanceof UserInterface) {
                    return true;
                }
                return false;
            case AccessType::TYPE_USERS:
                // handled by AclVoter
                return false;
            case AccessType::TYPE_FRIENDS:
                $objectIdent = $this->objectIdentity->getObjectIdentity();

                if ($objectIdent->getType() === 'IMDC\TerpTubeBundle\Entity\Forum') {
                    $forum = $this->entityManager->find('IMDCTerpTubeBundle:Forum', $objectIdent->getIdentifier());
                    if ($forum && $forum->getCreator()->getFriendsList()->contains($user)) {
                        return true;
                    }
                }

                if ($objectIdent->getType() === 'IMDC\TerpTubeBundle\Entity\Thread') {
                    $thread = $this->entityManager->find('IMDCTerpTubeBundle:Thread', $objectIdent->getIdentifier());
                    if ($thread && $thread->getCreator()->getFriendsList()->contains($user)) {
                        return true;
                    }
                }

                return false;
            case AccessType::TYPE_GROUP:
                $aces = $acl->getObjectAces();
                if (!$aces) {
                    return false;
                }

                $securityIdentity = $this->objectIdentity->getSecurityIdentities()[0];

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
        }
    }
}
