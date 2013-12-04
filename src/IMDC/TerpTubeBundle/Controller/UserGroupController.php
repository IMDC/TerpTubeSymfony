<?php

namespace IMDC\TerpTubeBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use FOS\UserBundle\Model\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use IMDC\TerpTubeBundle\Entity\UserGroup;
use IMDC\TerpTubeBundle\Form\Type\UserGroupType;

class UserGroupController extends Controller
{
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$usergroups = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->getPublicallyVisibleGroups();
		//->findAll();

		$response = $this->render('IMDCTerpTubeBundle:UserGroup:index.html.twig', array('usergroups' => $usergroups));
		return $response;
	}

	public function viewGroupAction($usergroupid)
	{
		$em = $this->getDoctrine()->getManager();

		$usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));

		$response = $this->render('IMDCTerpTubeBundle:UserGroup:viewgroup.html.twig', array('usergroup' => $usergroup));
		return $response;
	}

	public function createNewUserGroupAction(Request $request)
	{
		// check if user logged in
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$user = $this->getUser();

		$newusergroup = new UserGroup();

		$form = $this->createForm(new UserGroupType(), $newusergroup);

		$form->handleRequest($request);

		if ($form->isValid())
		{

			$newusergroup->setUserFounder($user);
			$newusergroup->addAdmin($user);
			$newusergroup->addMember($user);
			$user->addUserGroup($newusergroup);

			$em = $this->getDoctrine()->getManager();

			// request to persist objects to database
			$em->persist($newusergroup);
			$em->persist($user);

			// persist all objects to database
			$em->flush();

			$this->get('session')->getFlashBag()->add('notice', 'UserGroup created successfully!');
			return $this->redirect($this->generateUrl('imdc_groups_show_all'));

		}

		// form not valid, show the basic form
		return $this->render('IMDCTerpTubeBundle:UserGroup:new.html.twig', array('form' => $form->createView(),));

	}
	
	public function joinUserGroupAction(Request $request, $usergroupid) 
	{
	    // check if user logged in
	    if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
	    {
	        return $this->redirect($this->generateUrl('fos_user_security_login'));
	    }
	    
	    $user = $this->getUser();
	    $em = $this->getDoctrine()->getManager();
	    $usergroup = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->findOneBy(array('id' => $usergroupid));
	     
	    $usergroup->addMember($user);
	    $user->addUserGroup($usergroup);
	    
	    $em->persist($usergroup);
	    $em->persist($user);
	    
	    $em->flush();
	    
	    $response = $this->render('IMDCTerpTubeBundle:UserGroup:viewgroup.html.twig', array('usergroup' => $usergroup));
		return $response;
	}
	
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
	    
	    $response = $this->render('IMDCTerpTubeBundle:UserGroup:myGroups.html.twig', array('usergroups' => $usergroups));
	    return $response;
	}
}
