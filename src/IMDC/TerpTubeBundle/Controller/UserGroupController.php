<?php

namespace IMDC\TerpTubeBundle\Controller;
use Doctrine\ORM\Query\Expr\Join;
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
use IMDC\TerpTubeBundle\Controller\MediaChooserGatewayController;

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
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

        $recentGroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups(4);
        $usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups();

		//return $this->render('IMDCTerpTubeBundle:UserGroup:index.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Group:index.html.twig', array(
            'recentGroups' => $recentGroups,
            'groups' => $usergroups
        ));
	}

	/**
	 * View a specific usergroup
	 * 
	 * @param Request $request
	 * @param integer $usergroupid
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function viewGroupAction(Request $request, $usergroupid)
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
		$em = $this->getDoctrine()->getManager();

        $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));

        //return $this->render('IMDCTerpTubeBundle:UserGroup:viewgroup.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Group:view.html.twig', array(
            'group' => $usergroup
        ));
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

		// form not valid, show the basic form
		//return $this->render('IMDCTerpTubeBundle:UserGroup:editUserGroup.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group' => $usergroup,
            'uploadForms' => MediaChooserGatewayController::getUploadForms($this)
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
	
	/**
	 * Create a new usergroup and also create a new Forum with the usergroup's name
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function createNewUserGroupAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$newusergroup = new UserGroup();

		$form = $this->createForm(new UserGroupType(new Forum()), $newusergroup);
		
		$form->handleRequest($request);

		if ($form->isValid())
		{

			$newusergroup->setUserFounder($user);
			$newusergroup->addAdmin($user);
			$newusergroup->addMember($user);
			$newusergroup->getUserGroupForum()->setTitleText($newusergroup->getName());
			$newusergroup->getUserGroupForum()->setCreationDate(new \DateTime('now'));
			$newusergroup->getUserGroupForum()->setLastActivity(new \DateTime('now'));
			$newusergroup->getUserGroupForum()->setCreator($user);
			
			//TODO: make sure to set forum permissions on newly created forum to be 'group only' when permission are used
			
			$user->addUserGroup($newusergroup);

			$em = $this->getDoctrine()->getManager();

			// request to persist objects to database
			$em->persist($newusergroup);
			$em->persist($user);

			// persist all objects to database
			$em->flush();
			
			// creating the ACL
			$aclProvider = $this->get('security.acl.provider');
			$objectIdentity = ObjectIdentity::fromDomainObject($newusergroup);
			$acl = $aclProvider->createAcl($objectIdentity);
			
			// retrieving the security identity of the currently logged-in user
			$securityIdentity = UserSecurityIdentity::fromAccount($user);
			
			// grant owner access
			$acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
			$aclProvider->updateAcl($acl);

			$this->get('session')->getFlashBag()->add('info', 'UserGroup created successfully!');
			return $this->redirect($this->generateUrl('imdc_group_list'));

		}

		// form not valid, show the basic form
		//return $this->render('IMDCTerpTubeBundle:UserGroup:new.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Group:new.html.twig', array(
            'form' => $form->createView(),
            'uploadForms' => MediaChooserGatewayController::getUploadForms($this)
		));

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

        //return $this->render('IMDCTerpTubeBundle:UserGroup:myGroups.html.twig', array(
        return $this->render('IMDCTerpTubeBundle:_Group:index.html.twig', array(
            'groups' => $usergroups,
            'isMyGroups' => true
        ));
	}

    public function addMembersAction(Request $request, $usergroupid)
    {
        if (!$this->get('imdc_terptube.authentication_manager')->isAuthenticated($request)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($usergroupid);
        if (!$group) {
            //throw new Exception('group not found');
            return $this->redirect($this->generateUrl('imdc_group_view', array('usergroupid' => $usergroupid)));
        }

        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('EDIT', $group) === false) {
            throw new AccessDeniedException();
        }

        $userRepo = $em->getRepository('IMDCTerpTubeBundle:User');

        $defaultData = array('users' => new ArrayCollection());
        $form = $this->createFormBuilder($defaultData)
            ->add('userIds', 'collection', array('type' => 'integer'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $formUsers = $form->get('users');
            $user = $this->getUser();
            $addedMembers = [];
            $invitedMembers = [];

            foreach ($formUsers as $userId) {
                $newMember = $userRepo->find($userId);
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
                    $addedMembers[] = $newMember;
                } else {
                    // send an invitation

                    $invitedMembers[] = $newMember;
                }
            }

            $em->flush();

            $amCount = count($addedMembers);
            $imCount = count($invitedMembers);
            if ($amCount > 0 || $imCount > 0) {
                $fb = $this->get('session')->getFlashBag();
                if ($amCount > 0)
                    $fb->add('info', sprintf('Added %d members.', $amCount));
                if ($imCount > 0)
                    $fb->add('info', sprintf('Invited %d members.', $imCount));
            }
        }

        $groupMemberIds = [];
        foreach ($group->getMembers() as $member) {
            $groupMemberIds[] = $member->getId();
        }

        $qb = $userRepo->createQueryBuilder('u');
        $nonMembers = $qb->add('where', $qb->expr()->notIn('u.id', ':groupMemberIds'))
            ->setParameter('groupMemberIds', $groupMemberIds)
            ->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $nonMembers = $paginator->paginate(
            $nonMembers,
            $request->query->get('page', 1), /*page number*/
            8 /*limit per page*/
        );

        return $this->render('IMDCTerpTubeBundle:_Group:addMembers.html.twig', array(
            'group' => $group,
            'nonMembers' => $nonMembers
        ));
    }
}
