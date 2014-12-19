<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Entity\InvitationType;
use IMDC\TerpTubeBundle\Entity\Message;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\IdType;
use IMDC\TerpTubeBundle\Form\Type\MediaType;
use IMDC\TerpTubeBundle\Form\Type\UserGroupType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
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
     * @return Response
     */
    public function listAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		
		$em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IMDCTerpTubeBundle:UserGroup');
        $user = $this->getUser();
        $securityContext = $this->get('security.context');

        $groups = $repo->getViewableToUser($user, $securityContext);

		return $this->render('IMDCTerpTubeBundle:Group:index.html.twig', array(
            'groups' => $groups
        ));
	}

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function newAction(Request $request)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $group = new UserGroup();
        $form = $this->createForm(new UserGroupType(), $group);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();
            $group->setUserFounder($user);
            $group->addAdmin($user);
            //$group->addMember($user);

            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$group->getMedia()->contains($media))
                    $group->addMedia($media);*
                //FIXME override for now. at some point multiple media may be used
                $group->setMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('media')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

            $user->addUserGroup($group);

            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->persist($user);
            $em->flush();

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
	    // check if user logged in
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

        $parameters = array(
            'group' => $group,
            'forums' => $forums,
            'forumThreadCount' => $em->getRepository('IMDCTerpTubeBundle:Thread')
                    ->getViewableCountForForums($forums, $securityContext)
        );

        if ($securityContext->isGranted('EDIT', $group) === true) {
            $formBuilder = $this->getGenericIdFormBuilder();
            $formBuilder->setAction($this->generateUrl('imdc_group_delete_members', array('groupId' => $groupId)));
            $parameters['form'] = $formBuilder->getForm()->createView();
        }

        return $this->render('IMDCTerpTubeBundle:Group:view.html.twig', $parameters);
	}

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function editAction(Request $request, $groupId)
	{
	    // check if user logged in
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

		$form = $this->createForm(new UserGroupType(), $group);
		$form->handleRequest($request);
		
		if ($form->isValid()) {
            $user = $this->getUser();

            /*$media = $form->get('mediatextarea')->getData();
            if ($media) {
                if (!$user->getResourceFiles()->contains($media)) {
                    throw new AccessDeniedException(); //TODO more appropriate exception?
                }

                /*if (!$group->getMedia()->contains($media))
                    $group->addMedia($media);*
                //FIXME override for now. at some point multiple media may be used
                $group->setMedia($media);
            }*/

            //TODO 'currently' only your own media should be here, but check anyway
            if (!$user->ownsMediaInCollection($form->get('media')->getData())) {
                throw new AccessDeniedException(); //TODO more appropriate exception?
            }

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
     * @throws BadRequestHttpException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function deleteAction(Request $request, $groupId)
	{
        // if not ajax, throw an error
        if (!$request->isXmlHttpRequest() || !$request->isMethod('POST')) {
            throw new BadRequestHttpException('Only Ajax POST calls accepted');
        }

	    // check if user logged in
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
            'redirectUrl' => $this->generateUrl('imdc_group_my_groups')
        );

        return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listMyGroupsAction(Request $request)
    {
        // check if user logged in
        if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        return $this->render('IMDCTerpTubeBundle:Group:index.html.twig', array(
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
	    // check if user logged in
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
        // check if user logged in
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
        $group->removeMember($user);

        $em->persist($group);
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

    public function manageAction(Request $request, $groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new \Exception('group not found');
        }

        $style = $this->get('request')->query->get('style', 'list');

        $groupMemberIds = array();
        foreach ($group->getMembers() as $member) {
            $groupMemberIds[] = $member->getId();
        }

        $qb = $em->getRepository('IMDCTerpTubeBundle:User')->createQueryBuilder('u');
        $nonMembers = $qb->leftJoin('u.profile', 'p', Join::WITH, $qb->expr()->eq('u.profile', 'p.id'))
            ->where($qb->expr()->eq('p.profileVisibleToPublic', ':public'))
            ->andWhere($qb->expr()->notIn('u.id', ':groupMemberIds'))
            ->setParameters(array(
                'public' => 1,
                'groupMemberIds' => $groupMemberIds))
            ->getQuery()->getResult();

        // exclude users that have a pending invitation for the group
        $numNonMembers = count($nonMembers);
        for ($i=0; $i<$numNonMembers; $i++) {
            $member = $nonMembers[$i];
            $receivedInvites = $member->getReceivedInvitations();
            foreach ($receivedInvites as $receivedInvite) {
                if ($receivedInvite->getType()->isGroup()
                    && !$receivedInvite->getIsAccepted() && !$receivedInvite->getIsDeclined() && !$receivedInvite->getIsCancelled()) {
                    $groupCheck = InvitationController::getGroupFromInviteData($this, $receivedInvite);
                    if ($groupCheck && $groupCheck->getId() == $group->getId()) {
                        unset($nonMembers[$i]);
                        break; // stop at the first active invite. though more than one active invite should not be present
                    }
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $nonMembers = $paginator->paginate(
            $nonMembers,
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        return $this->render('IMDCTerpTubeBundle:Group:manage.html.twig', array(
            'group' => $group,
            'nonMembers' => $nonMembers,
            'style' => $style
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
                ||*/ $user->isUserOnMentorList($newMember)
                || $user->isUserOnMenteeList($newMember)
                || $user->isUserOnFriendsList($newMember)) {
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
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function addMembersAction(Request $request, $groupId)
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
        if ($securityContext->isGranted('EDIT', $group) === false) {
            throw new AccessDeniedException();
        }

        $userRepo = $em->getRepository('IMDCTerpTubeBundle:User');

        $form = $this->getGenericIdFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $formUsers = $form->get('users')->getData();
            $user = $this->getUser();
            $addedMembers = 0;
            $invitedMembers = 0;

            foreach ($formUsers as $formUser) {
                $newMember = $userRepo->find($formUser['id']);
                if (!$newMember) {
                    // user not found
                    continue;
                }

                if ($user->isUserOnMentorList($newMember)
                    || $user->isUserOnMenteeList($newMember)
                    || $user->isUserOnFriendsList($newMember)) {
                    // by pass invitation step and add users directly
                    //$group->addMember($newMember);
                    $newMember->addUserGroup($group);
                    //$em->persist($group);
                    $em->persist($newMember);
                    $addedMembers++;
                } else {
                    $this->sendGroupInvitation($user, $newMember, $group);
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

        $groupMemberIds = array();
        foreach ($group->getMembers() as $member) {
            $groupMemberIds[] = $member->getId();
        }

        $qb = $userRepo->createQueryBuilder('u');
        $nonMembers = $qb->leftJoin('u.profile', 'p', Join::WITH, $qb->expr()->eq('u.profile', 'p.id'))
            ->where($qb->expr()->eq('p.profileVisibleToPublic', ':public'))
            ->andWhere($qb->expr()->notIn('u.id', ':groupMemberIds'))
            ->setParameters(array(
                'public' => 1,
                'groupMemberIds' => $groupMemberIds))
            ->getQuery()->getResult();

        // exclude users that have a pending invitation for the group
        $numNonMembers = count($nonMembers);
        for ($i=0; $i<$numNonMembers; $i++) {
            $member = $nonMembers[$i];
            $receivedInvites = $member->getReceivedInvitations();
            foreach ($receivedInvites as $receivedInvite) {
                if ($receivedInvite->getType()->isGroup()
                    && !$receivedInvite->getIsAccepted() && !$receivedInvite->getIsDeclined() && !$receivedInvite->getIsCancelled()) {
                    $groupCheck = InvitationController::getGroupFromInviteData($this, $receivedInvite);
                    if ($groupCheck && $groupCheck->getId() == $group->getId()) {
                        unset($nonMembers[$i]);
                        break; // stop at the first active invite. though more than one active invite should not be present
                    }
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $nonMembers = $paginator->paginate(
            $nonMembers,
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        return $this->render('IMDCTerpTubeBundle:Group:addMembers.html.twig', array(
            'group' => $group,
            'nonMembers' => $nonMembers,
            'form' => $form->createView()
        ));
    }

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function deleteMembersAction(Request $request, $groupId)
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
        if ($securityContext->isGranted('EDIT', $group) === false) {
            throw new AccessDeniedException();
        }

        $form = $this->getGenericIdFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $formUsers = $form->get('users')->getData();
            $deletedMembers = 0;

            foreach ($formUsers as $formUser) {
                $member = $em->getRepository('IMDCTerpTubeBundle:User')->find($formUser['id']);
                if (!$member) {
                    // user not found
                    continue;
                }

                if ($group->isUserMemberOfGroup($member)) {
                    $group->removeMember($member);
                    $em->persist($group);
                    $deletedMembers++;
                }
            }

            $em->flush();

            if ($deletedMembers > 0) {
                $this->get('session')
                    ->getFlashBag()
                    ->add('info', sprintf('Deleted %d members.', $deletedMembers));
            }
        }

        return $this->redirect($this->generateUrl('imdc_group_view', array('groupId' => $groupId)));
    }

    /**
     * @return FormBuilder
     */
    private function getGenericIdFormBuilder() {
        //TODO replace with standard form type with user data transformer
        $defaultData = array('users' => new ArrayCollection());
        return $this->createFormBuilder($defaultData)
            ->add('users', 'collection', array(
                'type' => new IdType(),
                'label' => false,
                'allow_add' => true));
    }

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
}
