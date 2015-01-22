<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;
use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Entity\InvitationType;
use IMDC\TerpTubeBundle\Entity\Message;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\UserGroupManageSearchType;
use IMDC\TerpTubeBundle\Form\Type\UserGroupType;
use IMDC\TerpTubeBundle\Form\Type\UsersSelectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for UserGroup's which are essentially 'Groups' but the Group object is taken
 * @author paul
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class UserGroupController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IMDCTerpTubeBundle:UserGroup');
        $user = $this->getUser();
        $securityContext = $this->get('security.context');

        $groups = $repo->getViewableToUser($user, $securityContext);

        return $this->render('IMDCTerpTubeBundle:Group:list.html.twig', array(
            'groups' => $groups
        ));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function newAction(Request $request)
    {
        // check if user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();

        $usersSelectForm = $this->createForm(new UsersSelectType(), null, array('em' => $em));
        $usersSelectForm->handleRequest($request);

        $members = null;
        if ($usersSelectForm->isValid()) {
            $members = $usersSelectForm->get('users')->getData();
        }

        $group = new UserGroup();
        $formType = new UserGroupType();
        $hasMembersChildForm = !!$request->request->get($formType->getName() . '[members]', !!$members, true);
        $form = $this->createForm($formType, $group, array(
            'em' => $em,
            'hasMembersChildForm' => $hasMembersChildForm
        ));
        $form->handleRequest($request);

        if (!$form->isValid()) {
            if ($members) {
                //$form->get('members')->get('users')->setData($members);
                $form->get('members')->setData($members);
            }
        } else {
            $user = $this->getUser();
            $group->setUserFounder($user);
            $group->addAdmin($user);

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('media')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $group->setMediaDisplayOrder($form->get('media')->getViewData());

            $user->addUserGroup($group);

            $em->persist($group);
            $em->persist($user);
            $em->flush();

            if ($hasMembersChildForm) {
                //$members = $form->get('members')->get('users')->getData();
                $members = $form->get('members')->getData();
                $this->addMembers($group, $members);
            }

            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($group);
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $acl = $aclProvider->createAcl($objectIdentity);
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            $this->get('session')->getFlashBag()->add('info', 'Group created successfully!');

            return $this->redirect($this->generateUrl('imdc_group_view', array(
                'groupId' => $group->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Group:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function viewAction(Request $request, $groupId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('VIEW', $group) === false) {
            //redirect user to groups page if they dont have access to content
            return $this->redirect($this->generateUrl('imdc_group_list'));
        }

        $user = $this->getUser();
        $forumRepo = $em->getRepository('IMDCTerpTubeBundle:Forum');
        $sortParams = array(
            'sort' => $request->query->get('sort', 'f.lastActivity'),
            'direction' => $request->query->get('direction', 'desc')
        );

        $paginator = $this->get('knp_paginator');
        $forums = $paginator->paginate(
            $forumRepo->getViewableToUser($user, $securityContext, $sortParams, false, true, $group->getId()),
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        foreach ($sortParams as $key => $value) {
            $forums->setParam($key, $value);
        }

        return $this->render('IMDCTerpTubeBundle:Group:view.html.twig', array(
            'group' => $group,
            'forums' => $forums,
            'forumThreadCount' => $em->getRepository('IMDCTerpTubeBundle:Thread')
                ->getViewableCountForForums($forums, $securityContext)
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(Request $request, $groupId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted('EDIT', $group)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new UserGroupType(), $group, array(
            'em' => $em
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('media')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $group->setMediaDisplayOrder($form->get('media')->getViewData());

            $em->persist($group);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'Group edited successfully!');

            return $this->redirect($this->generateUrl('imdc_group_view', array(
                'groupId' => $group->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group' => $group
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Exception
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function deleteAction(Request $request, $groupId) //TODO api?
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('DELETE', $group) === false) {
            throw new AccessDeniedException();
        }

        foreach ($group->getMembers() as $member) {
            $member->removeUserGroup($group);
            // todo: send message/notification to each group member that group is deleted
        }

        $em->remove($group);

        $aclProvider = $this->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($group);
        $aclProvider->deleteAcl($objectIdentity);

        $em->flush();

        $content = array(
            'wasDeleted' => true,
            'redirectUrl' => $this->generateUrl('imdc_group_list')
        );

        return new Response(json_encode($content), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listMyGroupsAction(Request $request)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        return $this->render('IMDCTerpTubeBundle:Group:list.html.twig', array(
            'groups' => $this->getUser()->getUserGroups(),
            'isMyGroups' => true
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse
     * @throws \Exception
     */
    public function joinAction(Request $request, $groupId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        if (!$group->getOpenForNewMembers()) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();

        if ($group->getJoinByInvitationOnly()) {
            //TODO move to MessageController?
            $message = new Message();
            $message->addRecipient($group->getUserFounder());
            $message->setSubject('Request to join "' . $group->getName() . '"');
            $message->setContent('
                <strong>' . $user->getUsername() . '</strong> would like to join your group: <strong>' . $group->getName() . '</strong>.<br />
                <br />
                <a href="' . $this->generateUrl('imdc_group_invite_member', array(
                    'groupId' => $group->getId(),
                    'userId' => $user->getId()
                )) . '"><strong>Click here</strong></a> to accept their request and send an invitation.<br />
                <br />
                <strong>Note:</strong> If ' . $user->getUsername() . ' is on your mentor, mentee or friends lists, they will be instantly added to the group.<br />
                <br />
            ');
            $message->setOwner($user);
            $message->setSentDate(new \DateTime('now'));

            $user->addSentMessage($message);
            $group->getUserFounder()->addReceivedMessage($message);

            $em->persist($message);
            $em->persist($user);
            $em->persist($group->getUserFounder());
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success', 'A request to join this group has been sent to the founder.'
            );
        } else {
            /*if (!$group->getMembers()->contains($user)) {
                $group->addMember($user);
            }*/
            if (!$user->getUserGroups()->contains($group)) {
                $user->addUserGroup($group);
            }

            //$em->persist($group);
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success', 'You\'re now a member of this group.'
            );
        }

        return $this->redirect($this->generateUrl('imdc_group_view', array(
            'groupId' => $group->getId()
        )));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse
     * @throws \Exception
     */
    public function leaveAction(Request $request, $groupId)
    {
        // check if the user is logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $user = $this->getUser();
        $user->removeUserGroup($group);
        //$group->removeMember($user);

        //$em->persist($group);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('imdc_group_list'));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function messageAction(Request $request, $groupId)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        if (!$group->getMembers()->contains($this->getUser())) {
            throw new AccessDeniedException();
        }

        $group->removeMember($this->getUser()); // this is fine. not persisted

        return $this->forward('IMDCTerpTubeBundle:Message:new', array(
            'request' => $request,
            'recipients' => $group->getMembers()
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return Response
     * @throws \Exception
     */
    public function manageAction(Request $request, $groupId)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted('EDIT', $group)) {
            throw new AccessDeniedException();
        }

        $style = $this->get('request')->query->get('style', 'list');
        $activeTab = $this->get('request')->query->get('active_tab', '#tabMembers');

        // pagination
        $defaultPageNum = 1;
        $defaultPageLimit = 24;
        $paginatorParams = array(
            'members' => array(
                'knp' => array('pageParameterName' => 'page_m'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style,
                    'active_tab' => '#tabMembers'
                )
            ),
            'nonMembers' => array(
                'knp' => array('pageParameterName' => 'page_c'),
                'page' => $defaultPageNum,
                'pageLimit' => $defaultPageLimit,
                'urlParams' => array(
                    'style' => $style,
                    'active_tab' => '#tabCommunity'
                )
            )
        );
        //TODO consolidate?
        // extract paginator params from request
        foreach ($paginatorParams as &$params) {
            $params['page'] = $request->query->get($params['knp']['pageParameterName'], $params['page']);
        }
        $resetPage = function () use ($paginatorParams, $defaultPageNum) {
            foreach ($paginatorParams as &$params) {
                $params['page'] = $defaultPageNum;
            }
        };

        $user = $this->getUser();
        $userRepo = $em->getRepository('IMDCTerpTubeBundle:User');

        $removeForm = $this->createForm(new UsersSelectType('user_group_manage_remove'), null, array('em' => $em));
        $removeForm->handleRequest($request);

        if ($removeForm->isValid()) {
            // reset to default page regardless
            $resetPage();

            $members = $removeForm->get('users')->getData();
            $this->removeMembers($group, $members);
        }

        $addForm = $this->createForm(new UsersSelectType('user_group_manage_add'), null, array('em' => $em));
        $addForm->handleRequest($request);

        if ($addForm->isValid()) {
            // reset to default page regardless
            $resetPage();

            $members = $addForm->get('users')->getData();
            $this->addMembers($group, $members);
        }

        // prep query builders
        $membersQb = $userRepo->createQueryBuilder('u');
        $nonMembersQb = $userRepo->createQueryBuilder('u');

        // start: apply query filters
        $searchForm = $this->createForm(new UserGroupManageSearchType());
        $searchForm->handleRequest($request);

        if ($searchForm->isValid()) {
            // reset to default page regardless
            $resetPage();

            $filterMentors = $searchForm->get('mentors')->getData();
            $filterMentees = $searchForm->get('mentees')->getData();
            $filterFriends = $searchForm->get('friends')->getData();
            $username = $searchForm->get('username')->getData();

            // only filter if at least one is true. show all (don't filter) by default
            if ($filterMentors || $filterMentees || $filterFriends) {
                $filterIds = array();

                if ($filterMentors) {
                    foreach ($user->getMentorList() as $mentor) {
                        $filterIds[] = $mentor->getId();
                    }
                }

                if ($filterMentees) {
                    foreach ($user->getMenteeList() as $mentee) {
                        $filterIds[] = $mentee->getId();
                    }
                }

                if ($filterFriends) {
                    foreach ($user->getFriendsList() as $friend) {
                        $filterIds[] = $friend->getId();
                    }
                }

                $membersQb
                    ->where($membersQb->expr()->in('u.id', ':filterIds'))
                    ->setParameter('filterIds', $filterIds);

                $nonMembersQb
                    ->where($nonMembersQb->expr()->in('u.id', ':filterIds'))
                    ->setParameter('filterIds', $filterIds);
            }

            $membersQb
                ->andWhere($membersQb->expr()->like('u.username', ':username'))
                ->setParameter('username', '%' . $username . '%');

            $nonMembersQb
                ->andWhere($nonMembersQb->expr()->like('u.username', ':username'))
                ->setParameter('username', '%' . $username . '%');
        }
        // end: apply query filters

        // filter group members
        $members = $membersQb
            ->leftJoin('u.profile', 'p')
            ->innerJoin('u.userGroups', 'g')
            ->andWhere($membersQb->expr()->eq('g.id', ':groupId'))
            ->setParameter('groupId', $group->getId())
            ->getQuery()->getResult();

        // start: filter non group members
        $groupMemberIds = array();
        foreach ($group->getMembers() as $member) {
            $groupMemberIds[] = $member->getId();
        }

        $nonMembers = $nonMembersQb
            ->leftJoin('u.profile', 'p', Join::WITH, $nonMembersQb->expr()->eq('u.profile', 'p.id'))
            ->andWhere($nonMembersQb->expr()->eq('p.profileVisibleToPublic', ':public'))
            ->andWhere($nonMembersQb->expr()->notIn('u.id', ':groupMemberIds'))
            ->setParameter('public', 1)
            ->setParameter('groupMemberIds', $groupMemberIds)
            ->getQuery()->getResult();

        // exclude users that have a pending invitation for the group
        $numNonMembers = count($nonMembers);
        for ($i = 0; $i < $numNonMembers; $i++) {
            $member = $nonMembers[$i];
            $receivedInvites = $member->getReceivedInvitations();
            foreach ($receivedInvites as $receivedInvite) {
                if ($receivedInvite->getType()->isGroup()
                    && !$receivedInvite->getIsAccepted() && !$receivedInvite->getIsDeclined() && !$receivedInvite->getIsCancelled()
                ) {
                    $groupCheck = InvitationController::getGroupFromInviteData($this, $receivedInvite);
                    if ($groupCheck && $groupCheck->getId() == $group->getId()) {
                        unset($nonMembers[$i]);
                        break; // stop at the first active invite. though more than one active invite should not be present
                    }
                }
            }
        }
        // end: filter non group members

        // pagination
        $paginator = $this->get('knp_paginator');
        //TODO consolidate?
        $paginate = function ($object, $name) use ($paginatorParams, $paginator) {
            $params = $paginatorParams[$name];

            $paginated = $paginator->paginate(
                $object,
                $params['page'],
                $params['pageLimit'],
                $params['knp']
            );

            if (array_key_exists('urlParams', $params)) {
                foreach ($params['urlParams'] as $key => $value) {
                    $paginated->setParam($key, $value);
                }
            }

            return $paginated;
        };

        $members = $paginate($members, 'members');
        $nonMembers = $paginate($nonMembers, 'nonMembers');

        return $this->render('IMDCTerpTubeBundle:Group:manage.html.twig', array(
            'group' => $group,
            'style' => $style,
            'activeTab' => $activeTab,
            'members' => $members,
            'nonMembers' => $nonMembers,
            'searchForm' => $searchForm->createView(),
            'removeForm' => $removeForm->createView(),
            'addForm' => $addForm->createView()
        ));
    }

    public function inviteMemberAction(Request $request, $groupId, $userId)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        $newMember = $em->getRepository('IMDCTerpTubeBundle:User')->find($userId);
        if (!$group || !$newMember) {
            throw new \Exception('group/user not found');
        }

        if ($group->getMembers()->contains($newMember)) {
            $this->get('session')->getFlashBag()->add(
                'info', $newMember->getUsername() . ' is already a member of the ' . $group->getName() . ' group.'
            );
        } else {
            $user = $this->getUser();
            if (/*!$group->getJoinByInvitationOnly() // is invitation only no longer flagged on the group?
                ||*/
                $user->isUserOnMentorList($newMember)
                || $user->isUserOnMenteeList($newMember)
                || $user->isUserOnFriendsList($newMember)
            ) {
                // by pass invitation step and add users directly
                //$group->addMember($newMember);
                $newMember->addUserGroup($group);
                //$em->persist($group);
                $em->persist($newMember);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success', 'Added ' . $newMember->getUsername() . ' to the ' . $group->getName() . ' group.'
                );
            } else {
                $this->sendGroupInvitation($user, $newMember, $group);

                $this->get('session')->getFlashBag()->add(
                    'success', 'Invited ' . $newMember->getUsername() . ' to join the ' . $group->getName() . ' group.'
                );
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param $sender
     * @param $recipient
     * @param $group
     */
    private function sendGroupInvitation($sender, $recipient, $group)
    {
        $em = $this->getDoctrine()->getManager();

        //TODO move to InvitationController?
        $invitation = new Invitation();
        $invitation->setCreator($sender);
        $invitation->setRecipient($recipient);
        $invitation->setType($em->getRepository('IMDCTerpTubeBundle:InvitationType')->find(InvitationType::TYPE_GROUP));
        $invitation->setData(array(
            'groupId' => $group->getId()
        ));

        $sender->addCreatedInvitation($invitation);
        $recipient->addReceivedInvitation($invitation);

        $em->persist($invitation);
        $em->persist($sender);
        $em->persist($recipient);
        $em->flush();
    }

    /**
     * @param $group
     * @param $members
     */
    private function removeMembers($group, $members)
    {
        $em = $this->getDoctrine()->getManager();
        $deletedMembers = 0;

        foreach ($members as $member) {
            if ($group->isUserMemberOfGroup($member)) {
                $member->removeUserGroup($group);
                $em->persist($member);
                $deletedMembers++;
            }
        }

        $em->flush();

        if ($deletedMembers > 0) {
            $this->get('session')
                ->getFlashBag()
                ->add('info', sprintf('Removed %d members.', $deletedMembers));
        }
    }

    /**
     * @param $group
     * @param $members
     */
    private function addMembers($group, $members)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $addedMembers = 0;
        $invitedMembers = 0;

        foreach ($members as $member) {
            if ($user->isUserOnMentorList($member)
                || $user->isUserOnMenteeList($member)
                || $user->isUserOnFriendsList($member)
            ) {
                // by pass invitation step and add users directly
                //$group->addMember($newMember);
                $member->addUserGroup($group);
                //$em->persist($group);
                $em->persist($member);
                $addedMembers++;
            } else {
                $this->sendGroupInvitation($user, $member, $group);
                $invitedMembers++;
            }
        }

        $em->flush();

        if ($addedMembers > 0 || $invitedMembers > 0) {
            $fb = $this->get('session')->getFlashBag();
            if ($addedMembers > 0)
                $fb->add('info', sprintf('Added %d members.', $addedMembers));
            if ($invitedMembers > 0)
                $fb->add('info', sprintf('Invited %d members.', $invitedMembers));
        }
    }
}
