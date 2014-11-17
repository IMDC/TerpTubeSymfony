<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Entity\InvitationType;
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

        $recentGroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups(4); //TODO revise
        $usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups(); //TODO revise

		return $this->render('IMDCTerpTubeBundle:Group:index.html.twig', array(
            'recentGroups' => $recentGroups,
            'groups' => $usergroups
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
            $group->addMember($user);

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
            'form' => $form->createView(),
            //'uploadForms' => MyFilesGatewayController::getUploadForms($this),
            //'fileUploadForm' => $this->createForm(new MediaType())->createView()
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

        $paginator = $this->get('knp_paginator');
        $forums = $paginator->paginate(
            $group->getForums(),
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        //FIXME this seems too costly just for the result of numeric convenience
        $forumThreadCount = array();
        $threadRepo = $em->getRepository('IMDCTerpTubeBundle:Thread');
        foreach ($forums as $forum) {
            $forumThreadCount[] = count($threadRepo->getViewableToUser($securityContext, $forum->getId()));
        }

        $parameters = array(
            'group' => $group,
            'forums' => $forums,
            'forumThreadCount' => $forumThreadCount
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
            'group' => $group,
            //'uploadForms' => MyFilesGatewayController::getUploadForms($this),
            //'fileUploadForm' => $this->createForm(new MediaType())->createView()
        ));
	}

    /**
     * @param Request $request
     * @param $groupId
     * @return RedirectResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     */
    public function deleteAction(Request $request, $groupId)
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
	    if ($securityContext->isGranted('DELETE', $group) === false) {
	        throw new AccessDeniedException();
	    }
	    
	    foreach ($group->getMembers() as $member) {
            $member->removeUserGroup($group);
	        // todo: send message/notification to each group member that group is deleted
	    }

        $em->remove($group);
        $em->flush();

	    $aclProvider = $this->get('security.acl.provider');
	    $objectIdentity = ObjectIdentity::fromDomainObject($group);
	    $aclProvider->deleteAcl($objectIdentity);

	    $this->get('session')->getFlashBag()->add('info', 'Group deleted!');

	    return $this->redirect($this->generateUrl('imdc_group_list'));
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

        $user = $this->getUser();
        if (!$user->getUserGroups()->contains($group)) {
            $user->addUserGroup($group);
        }
        if (!$group->getMembers()->contains($user)) {
            $group->addMember($user);
        }

	    $em->persist($group);
	    $em->persist($user);
	    $em->flush();

        return $this->redirect($this->generateUrl('imdc_group_list'));
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
                    $group->addMember($newMember);
                    $em->persist($group);
                    $addedMembers++;
                } else {
                    // send an invitation
                    //TODO move to InvitationController
                    $invitation = new Invitation();
                    $invitation->setCreator($user);
                    $invitation->setRecipient($newMember);
                    $invitation->setType($em->getRepository('IMDCTerpTubeBundle:InvitationType')->find(InvitationType::TYPE_GROUP));
                    $invitation->setData(array(
                        'groupId' => $group->getId()
                    ));

                    $user->addCreatedInvitation($invitation);
                    $newMember->addReceivedInvitation($invitation);

                    $em->persist($invitation);
                    $em->persist($user);
                    $em->persist($newMember);

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
}
