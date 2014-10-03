<?php

namespace IMDC\TerpTubeBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use IMDC\TerpTubeBundle\Entity\Invitation;
use IMDC\TerpTubeBundle\Entity\InvitationType;
use IMDC\TerpTubeBundle\Form\Type\IdType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\UserBundle\Model\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\UserGroupType;
use IMDC\TerpTubeBundle\Entity\Forum;
use IMDC\TerpTubeBundle\Controller\MyFilesGatewayController;

/**
 * Controller for UserGroup's which are essentially 'Groups' but the Group object is taken
 * @author paul
 *
 */
class UserGroupController extends Controller
{
    /**
     * Lists all usergroups
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function listAction()
	{
		$em = $this->getDoctrine()->getManager();

        $recentGroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups(4);
        $usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups();

		return $this->render('IMDCTerpTubeBundle:Group:index.html.twig', array(
            'recentGroups' => $recentGroups,
            'groups' => $usergroups
        ));
	}

    /**
     * Create a new usergroup and also create a new Forum with the usergroup's name
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
            $user->addUserGroup($group);

            $group->setUserFounder($user);
            $group->addAdmin($user);
            $group->addMember($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->persist($user);
            $em->flush();

            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($group);
            $acl = $aclProvider->createAcl($objectIdentity);

            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            $this->get('session')->getFlashBag()->add('info', 'UserGroup created successfully!');

            return $this->redirect($this->generateUrl('imdc_group_view', array(
                'usergroupid' => $group->getId()
            )));
        }

        return $this->render('IMDCTerpTubeBundle:Group:new.html.twig', array(
            'form' => $form->createView()/*,
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)*/
        ));
    }

	public function viewAction(Request $request, $usergroupid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
		$em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($usergroupid);
        if (!$group) {
            throw new Exception('group not found');
        }

        $paginator = $this->get('knp_paginator');
        $forums = $paginator->paginate(
            $group->getForums(),
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        $parameters = array(
            'group' => $group,
            'forums' => $forums
        );

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $group) === true) {
            $formBuilder = $this->getGenericIdFormBuilder();
            $formBuilder->setAction($this->generateUrl('imdc_group_delete_members', array('groupId' => $usergroupid)));
            $parameters['form'] = $formBuilder->getForm()->createView();
        }

        return $this->render('IMDCTerpTubeBundle:Group:view.html.twig', $parameters);
	}

	/**
	 * Edit a specific usergroup
	 * 
	 * @param Request $request
	 * @param unknown $usergroupid
	 * @throws AccessDeniedException
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function editUserGroupAction(Request $request, $usergroupid)
	{
	    // check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
        $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));
		
		// check if user has permission to edit based on ACL
		$securityContext = $this->get('security.context');
		 
		// check for edit access using ACL
		if (false === $securityContext->isGranted('EDIT', $usergroup)) {
		    throw new AccessDeniedException();
		}

		$form = $this->createForm(new UserGroupType(), $usergroup);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
		    $em->persist($usergroup);
		    
		    $em->flush();
		    
		    $this->get('session')->getFlashBag()->add('info', 'UserGroup edited successfully!');
		    return $this->redirect($this->generateUrl('imdc_group_view', array('usergroupid' => $usergroupid)));
		}

		return $this->render('IMDCTerpTubeBundle:Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group' => $usergroup,
            'uploadForms' => MyFilesGatewayController::getUploadForms($this)
        ));
	}
	
	/**
	 * Delete a specific usergroup
	 * 
	 * @param Request $request
	 * @param unknown $usergroupid
	 * @throws AccessDeniedException
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function deleteUserGroupAction(Request $request, $usergroupid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    $em = $this->getDoctrine()->getManager();
	    $user = $this->getUser();
	    $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));
	    
	    // check if user has permission to edit based on ACL
	    $securityContext = $this->get('security.context');
	    	
	    
	    // check for delete access using ACL
	    if (false === $securityContext->isGranted('DELETE', $usergroup)) {
	        throw new AccessDeniedException();
	    }
	    
	    foreach($usergroup->getMembers() as $groupmember) {
	        $groupmember->removeUserGroup($usergroup);
	        // todo: send message/notification to each group member that group is deleted
	    }

	    // delete ACL info
	    $aclProvider = $this->get('security.acl.provider');
	    $objectIdentity = ObjectIdentity::fromDomainObject($usergroup);
	    $aclProvider->deleteAcl($objectIdentity);
	    
	    $em->remove($usergroup);
	    $em->flush();
	    
	    $this->get('session')->getFlashBag()->add('info', 'UserGroup deleted!');
	    return $this->redirect($this->generateUrl('imdc_group_list'));
	}
	
	public function joinUserGroupAction(Request $request, $usergroupid) 
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    $em = $this->getDoctrine()->getManager();
	    
	    $user = $this->getUser();
	    $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));
	     
	    $user->addUserGroup($usergroup);
	    $usergroup->addMember($user);
	    
	    // request to persist objects to database
	    $em->persist($usergroup);
	    $em->persist($user);
	    
	    // flush objects to database
	    $em->flush();

        return $this->redirect($this->generateUrl('imdc_group_list'));
	}
	
	/**
	 * Show the user groups for the currently logged in user
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function myGroupShowAction(Request $request)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    $user = $this->getUser();
	    
	    $em = $this->getDoctrine()->getManager();
	    //$usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getGroupsForUser($user);
	    $usergroups = $user->getUserGroups();
	    //->findAll();

        return $this->render('IMDCTerpTubeBundle:Group:index.html.twig', array(
            'groups' => $usergroups,
            'isMyGroups' => true
        ));
	}

    public function addMembersAction(Request $request, $groupId)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new Exception('group not found');
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
        for ($i=0; $i<count($nonMembers); $i++) {
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

    public function deleteMembersAction(Request $request, $groupId)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
        if (!$group) {
            throw new Exception('group not found');
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
            $deletedMembers = 0;

            foreach ($formUsers as $formUser) {
                $member = $userRepo->find($formUser['id']);
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

        return $this->redirect($this->generateUrl('imdc_group_view', array('usergroupid' => $groupId)));
    }

    private function getGenericIdFormBuilder() {
        $defaultData = array('users' => new ArrayCollection());
        return $this->createFormBuilder($defaultData)
            ->add('users', 'collection', array(
                'type' => new IdType(),
                'label' => false,
                'allow_add' => true));
    }
}
